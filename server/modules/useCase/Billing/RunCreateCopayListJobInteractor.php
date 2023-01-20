<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\File\FileInputStream;
use Domain\File\FileStorage;
use Domain\File\TemporaryFiles;
use Domain\Job\Job as DomainJob;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\Permission\Permission;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\File\GenerateFileNameUseCase;
use UseCase\Job\RunJobUseCase;
use ZipArchive;

/**
 * 利用者負担額一覧表作成ジョブ実行ユースケース実装.
 */
final class RunCreateCopayListJobInteractor implements RunCreateCopayListJobUseCase
{
    use Logging;

    private const STORE_TO = 'exported';
    private const FILENAME_LENGTH = 16;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupDwsBillingUseCase
     * @param \UseCase\Billing\GenerateCopayListPdfUseCase $useCase
     * @param \UseCase\File\GenerateFileNameUseCase $generateFileNameUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     * @param \ZipArchive $zipArchive
     * @param \Domain\File\TemporaryFiles $temporaryFiles
     * @param \Domain\File\FileStorage $fileStorage
     * @param \Domain\Office\OfficeRepository $officeRepository
     */
    public function __construct(
        private RunJobUseCase $runJobUseCase,
        private SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase,
        private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        private LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        private GenerateCopayListPdfUseCase $useCase,
        private GenerateFileNameUseCase $generateFileNameUseCase,
        private IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
        private ZipArchive $zipArchive,
        private TemporaryFiles $temporaryFiles,
        private FileStorage $fileStorage,
        private OfficeRepository $officeRepository
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, int $billingId, array $ids, bool $isDivided): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $billingId, $ids, $isDivided): array {
                $statements = $this->lookupDwsBillingStatement($context, $ids);
                $bundles = $this->lookupDwsBillingBundles($context, $billingId, $statements);
                $billing = $this->lookupDwsBilling($context, $billingId);
                if ($isDivided) {
                    $downloadFilename = $this->createName($billing, $bundles->head()->providedIn, 'copay_list_zip');
                    $temporaryPath = $this->temporaryFiles->create('zip-', '-zip')->getPathname();
                    $open = $this->zipArchive->open($temporaryPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    if ($open === true) {
                        $path = $this->storeZipFile($context, $bundles, $statements, $billing, $temporaryPath);
                    } else {
                        throw new FileIOException("Failed to open zip file: {$temporaryPath} (errorCode:{$open})");
                    }
                } else {
                    $path = $this->useCase->handle($context, $billing, $bundles, $statements);
                    $downloadFilename = $this->createName($billing, $bundles->head()->providedIn, 'copay_list_pdf');
                }

                $this->logger()->info(
                    '利用者負担額一覧表作成ジョブ終了',
                    ['filename' => $downloadFilename] + $context->logContext()
                );
                return [
                    'uri' => $context->uri("copay-lists/download/{$path}"),
                    'filename' => $downloadFilename,
                ];
            }
        );
    }

    /**
     * ZIPアーカイブにファイルを追加し保管する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq $statements
     * @param \Domain\Billing\DwsBilling $billing
     * @param string $temporaryPath
     * @return string
     */
    private function storeZipFile(Context $context, Seq $bundles, Seq $statements, DwsBilling $billing, string $temporaryPath): string
    {
        $providedIn = $bundles->head()->providedIn;
        $certificationsMap = $this->identifyCertifications($context, $statements, $providedIn);
        foreach ($certificationsMap as $officeId => $certifications) {
            $xs = $certifications->flatMap(
                fn (DwsCertification $certification): Option => $statements->find(
                    function (DwsBillingStatement $statement) use ($certification): bool {
                        return $certification->userId === $statement->user->userId;
                    }
                )
            );
            $office = $this->getOffice($officeId);
            $filename = $this->createPdfName($billing, $office, $providedIn);
            $pdfPath = $this->useCase->handle($context, $billing, $bundles, $xs);
            /** @var \SplFileInfo $pdfFile */
            $pdfFile = $this->fileStorage->fetch($pdfPath)->getOrElse(function () use ($pdfPath): never {
                throw new RuntimeException("Filed to fetch file: {$pdfPath}");
            });
            $this->zipArchive->addFile($pdfFile->getPathname(), $filename);
        }
        $this->zipArchive->close();
        $file = FileInputStream::from(basename($temporaryPath), $temporaryPath);
        return $this->fileStorage->store(self::STORE_TO, $file)->getOrElse(function (): never {
            throw new FileIOException('Failed to store file');
        });
    }

    /**
     * 障害福祉サービス：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $ids
     * @return \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq
     */
    private function lookupDwsBillingStatement(Context $context, array $ids): Seq
    {
        $entities = $this->lookupDwsBillingStatementUseCase->handle($context, Permission::viewBillings(), ...$ids);
        if (count($entities) !== count($ids)) {
            $x = implode(',', $ids);
            throw new NotFoundException("DwsBillingStatement ({$x}) not found");
        }
        return $entities;
    }

    /**
     * 障害福祉サービス：明細書に紐づく障害福祉サービス：請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq
     */
    private function lookupDwsBillingBundles(Context $context, int $billingId, Seq $statements): Seq
    {
        $bundleIds = $statements
            ->map(fn (DwsBillingStatement $statement): int => $statement->dwsBillingBundleId)
            ->distinct()
            ->toArray();
        return $this->lookupDwsBillingBundleUseCase->handle(
            $context,
            Permission::viewBillings(),
            $billingId,
            ...$bundleIds
        );
    }

    /**
     * 障害福祉サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Billing\DwsBilling
     */
    private function lookupDwsBilling(Context $context, int $id): DwsBilling
    {
        return $this->lookupDwsBillingUseCase->handle($context, Permission::viewBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBilling({$id}) not found.");
            });
    }

    /**
     * ダウンロード用のファイル名を生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Common\Carbon $providedIn
     * @param string $filename
     * @return string
     */
    private function createName(DwsBilling $billing, Carbon $providedIn, string $filename): string
    {
        $placeholders = [
            'office' => $billing->office->abbr,
            'providedIn' => $providedIn,
        ];
        return $this->generateFileNameUseCase->handle($filename, $placeholders);
    }

    /**
     * 分割時のPDFファイル名を生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Office\Office $office
     * @return string
     */
    private function createPdfName(DwsBilling $billing, Office $office, Carbon $providedIn): string
    {
        $placeholders = [
            'office' => $billing->office->abbr,
            'toOffice' => strlen($office->abbr) === 0 ? $office->name : $office->abbr,
            'providedIn' => $providedIn,
        ];
        return $this->generateFileNameUseCase->handle('copay_list_divided_pdf', $placeholders);
    }

    /**
     * 各明細書に対応する障害福祉サービス受給者証を取得（特定）し, 事業所 ID をキーにしたマップを返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingStatement&\ScalikePHP\Seq $statements
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification[][]&\ScalikePHP\Map&\ScalikePHP\Seq[]
     */
    private function identifyCertifications(Context $context, Seq $statements, Carbon $providedIn): Map
    {
        $certifications = $statements->map(
            fn (DwsBillingStatement $x): DwsCertification => $this->identifyDwsCertificationUseCase
                ->handle($context, $x->user->userId, $providedIn)
                ->getOrElse(function () use ($providedIn, $x): never {
                    throw new NotFoundException(
                        "DwsCertification(userId={$x->user->userId}, providedIn={$providedIn->format('Y-m')}) not found"
                    );
                })
        );
        return $certifications->groupBy(fn (DwsCertification $x): int => $x->copayCoordination->officeId);
    }

    /**
     * 事業所の取得.
     *
     * @param int $id
     * @return \Domain\Office\Office
     */
    private function getOffice(int $id): Office
    {
        return $this->officeRepository
            ->lookup($id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new RuntimeException("CopayCoordination Office({$id}) not found.");
            });
    }
}

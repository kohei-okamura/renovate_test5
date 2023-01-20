<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Config\Config;
use Domain\Context\Context;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;
use UseCase\File\GenerateFileNameUseCase;
use UseCase\File\StorePdfUseCase;

/**
 * 障害福祉サービス：利用者負担上限額管理結果票 PDF 生成ユースケース実装.
 */
class CreateDwsBillingCopayCoordinationPdfInteractor implements CreateDwsBillingCopayCoordinationPdfUseCase
{
    use UniqueTokenSupport;

    private const FILE_PLACEHOLDER_YEAR_MONTH_FORMAT = 'Ym';
    private const STORE_TO = 'artifacts'; // TODO: DEV-5732
    private const TEMPLATE = 'pdfs.billings.dws-billing-copay-coordination.index';
    private const TOKEN_MAX_RETRY_COUNT = 100;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationPdfInteractor} constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamUseCase $buildPdfParamUseCase
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     * @param GenerateFileNameUseCase $generateFileNameUseCase
     */
    public function __construct(
        private Config $config,
        TokenMaker $tokenMaker,
        private BuildDwsBillingCopayCoordinationPdfParamUseCase $buildPdfParamUseCase,
        private GenerateFileNameUseCase $generateFileNameUseCase,
        private StorePdfUseCase $storePdfUseCase
    ) {
        $this->tokenMaker = $tokenMaker;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): Option
    {
        $providedInString = $bundles->map(function (DwsBillingBundle $x): string {
            return $x->providedIn->toDateString();
        })->distinct();
        // 請求単位の一覧に複数のサービス提供年月が含まれている場合は異常なケースなので例外とする
        if ($providedInString->count() !== 1) {
            throw new LogicException('DwsBillingBundle contains multiple providedIn.');
        }
        $providedIn = $bundles->head()->providedIn;
        return $this->store($context, $billing, $bundles)
            ->map(fn (string $path): DwsBillingFile => new DwsBillingFile(
                name: $this->createName($billing, $providedIn),
                path: $path,
                token: $this->createUniqueToken(TokenMaker::DEFAULT_TOKEN_LENGTH, self::TOKEN_MAX_RETRY_COUNT),
                mimeType: MimeType::pdf(),
                createdAt: Carbon::now(),
                downloadedAt: null,
            ));
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        // TODO: DEV-4531 請求関連ファイルのトークン一意性担保について検討・対応
        return true;
    }

    /**
     * PDF を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return \ScalikePHP\Option&string[]
     */
    private function store(Context $context, DwsBilling $billing, Seq $bundles): Option
    {
        $param = $this->buildPdfParamUseCase->handle($context, $billing, $bundles);
        if ($this->shouldCreateCopayCoordinationPdf($param)) {
            $path = $this->storePdfUseCase->handle(
                $context,
                self::STORE_TO,
                self::TEMPLATE,
                $param
            );
            return Option::some($path);
        } else {
            return Option::none();
        }
    }

    /**
     * ダウンロード用のファイル名を生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Common\Carbon $providedIn
     * @return string
     */
    private function createName(DwsBilling $billing, Carbon $providedIn): string
    {
        $placeholders = [
            'office' => $billing->office->abbr,
            'transactedIn' => $billing->transactedIn,
            'providedIn' => $providedIn,
        ];
        return $this->generateFileNameUseCase->handle('dws_copay_coordination_pdf', $placeholders);
    }

    /**
     * 利用者負担上限額管理結果票PDFを生成する必要があるか判定する.
     *
     * @param array $param
     * @return bool
     */
    private function shouldCreateCopayCoordinationPdf(array $param): bool
    {
        return Seq::fromArray($param['bundles'] ?? [])
            ->flatMap(fn (array $bundle) => $bundle['copayCoordinations'])
            ->nonEmpty();
    }
}

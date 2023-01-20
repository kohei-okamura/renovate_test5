<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Lib\Logging;
use ScalikePHP\Option;

/**
 * 利用者負担上限額管理結果更新ユースケース実装.
 */
class UpdateDwsBillingStatementCopayCoordinationInteractor implements UpdateDwsBillingStatementCopayCoordinationUseCase
{
    use Logging;

    private BuildDwsBillingStatementForUpdateUseCase $buildUseCase;
    private DwsBillingStatementRepository $repository;
    private EnsureDwsBillingBundleUseCase $ensureUseCase;
    private GetDwsBillingStatementInfoUseCase $getInfoUseCase;
    private LookupDwsBillingStatementUseCase $lookupUseCase;
    private DwsCertificationRepository $certificationRepository;
    private OfficeRepository $officeRepository;
    private TransactionManager $transaction;
    private UpdateDwsBillingInvoiceUseCase $updateInvoiceUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase $buildUseCase
     * @param \Domain\Billing\DwsBillingStatementRepository $repository
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureUseCase
     * @param \UseCase\Billing\GetDwsBillingStatementInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupUseCase
     * @param \Domain\DwsCertification\DwsCertificationRepository $certificationRepository
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\TransactionManagerFactory $factory
     * @param \UseCase\Billing\UpdateDwsBillingInvoiceUseCase $updateInvoiceUseCase
     */
    public function __construct(
        BuildDwsBillingStatementForUpdateUseCase $buildUseCase,
        DwsBillingStatementRepository $repository,
        EnsureDwsBillingBundleUseCase $ensureUseCase,
        GetDwsBillingStatementInfoUseCase $getInfoUseCase,
        LookupDwsBillingStatementUseCase $lookupUseCase,
        DwsCertificationRepository $certificationRepository,
        OfficeRepository $officeRepository,
        TransactionManagerFactory $factory,
        UpdateDwsBillingInvoiceUseCase $updateInvoiceUseCase
    ) {
        $this->buildUseCase = $buildUseCase;
        $this->repository = $repository;
        $this->ensureUseCase = $ensureUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->certificationRepository = $certificationRepository;
        $this->officeRepository = $officeRepository;
        $this->transaction = $factory->factory($repository);
        $this->updateInvoiceUseCase = $updateInvoiceUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, int $bundleId, int $id, Option $values): array
    {
        /** @var \Domain\Billing\DwsBillingStatement $entity */
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $billingId, $bundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingStatement({$id}) not found.");
            });

        // $values が none の時は、上限管理区分 => 入力中、上限管理結果 => 未設定 に戻す
        [$copayCoordination, $copayCoordinationStatus] = (function (Option $option, DwsBillingStatement $entity): array {
            return $option
                ->map(fn (array $values): array => [
                    $entity->copayCoordination
                        ? $entity->copayCoordination->copy($values)
                        : $this->buildCopayCoordination($entity, $values),
                    DwsBillingStatementCopayCoordinationStatus::fulfilled(),
                ])
                ->getOrElseValue([null, DwsBillingStatementCopayCoordinationStatus::checking()]);
        })($values, $entity);

        $entityForUpdate = $entity->copy([
            'copayCoordination' => $copayCoordination,
            'copayCoordinationStatus' => $copayCoordinationStatus,
            'status' => DwsBillingStatus::ready(),
            'updatedAt' => Carbon::now(),
        ]);
        $entityForStore = $this->buildUseCase->handle($context, $entityForUpdate);

        $x = $this->transaction->run(function () use ($entityForStore, $context, $billingId, $bundleId): DwsBillingStatement {
            $statement = $this->repository->store($entityForStore);
            $this->updateInvoiceUseCase->handle($context, $billingId, $bundleId);
            return $statement;
        });

        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $this->getInfoUseCase->handle($context, $billingId, $bundleId, $id);
    }

    /**
     * 障害福祉サービス明細書：上限管理結果 の構築.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param array $values リクエストpayload
     * @return \Domain\Billing\DwsBillingStatementCopayCoordination
     */
    private function buildCopayCoordination(
        DwsBillingStatement $statement,
        array $values
    ): DwsBillingStatementCopayCoordination {
        $certification = $this->getCertification($statement->user->dwsCertificationId);
        $office = DwsBillingOffice::from($this->getOffice($certification->copayCoordination->officeId));
        return DwsBillingStatementCopayCoordination::create(
            compact('office')
            + $values
        );
    }

    /**
     * 障害福祉サービス受給者証 の取得.
     *
     * @param int $id
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function getCertification(int $id): DwsCertification
    {
        return $this->certificationRepository
            ->lookup($id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new RuntimeException("DwsCertification({$id}) not found.");
            });
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

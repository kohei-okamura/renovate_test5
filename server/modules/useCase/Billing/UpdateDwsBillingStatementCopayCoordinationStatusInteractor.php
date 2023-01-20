<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement as Statement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス：明細書：上限管理区分 更新ユースケース実装.
 */
class UpdateDwsBillingStatementCopayCoordinationStatusInteractor implements UpdateDwsBillingStatementCopayCoordinationStatusUseCase
{
    use Logging;

    private DwsBillingStatementRepository $repository;
    private EnsureDwsBillingBundleUseCase $ensureUseCase;
    private GetDwsBillingStatementInfoUseCase $getInfoUseCase;
    private LookupDwsBillingStatementUseCase $lookupUseCase;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \Domain\Billing\DwsBillingStatementRepository $repository
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureUseCase
     * @param \UseCase\Billing\GetDwsBillingStatementInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupUseCase
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DwsBillingStatementRepository $repository,
        EnsureDwsBillingBundleUseCase $ensureUseCase,
        GetDwsBillingStatementInfoUseCase $getInfoUseCase,
        LookupDwsBillingStatementUseCase $lookupUseCase,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->ensureUseCase = $ensureUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id,
        DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus
    ): array {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $billingId, $bundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingStatement({$id}) not found.");
            });

        $x = $this->transaction->run(
            fn (): Statement => $this->repository->store($entity->copy([
                'copayCoordination' => null,
                'copayCoordinationStatus' => $copayCoordinationStatus,
                'status' => DwsBillingStatus::ready(),
                'updatedAt' => Carbon::now(),
            ]))
        );

        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $this->getInfoUseCase->handle($context, $billingId, $bundleId, $id);
    }
}

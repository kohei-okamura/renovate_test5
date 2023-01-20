<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票 状態一括更新ユースケース実装.
 */
class BulkUpdateDwsBillingServiceReportStatusInteractor implements BulkUpdateDwsBillingServiceReportStatusUseCase
{
    use Logging;

    private SimpleLookupDwsBillingServiceReportUseCase $lookupServiceReportUseCase;
    private DwsBillingServiceReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\UserBilling\BulkUpdateDwsBillingServiceReportStatusInteractor} Constructor.
     *
     * @param \UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase $lookupServiceReportUseCase
     * @param \Domain\Billing\DwsBillingServiceReportRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        SimpleLookupDwsBillingServiceReportUseCase $lookupServiceReportUseCase,
        DwsBillingServiceReportRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupServiceReportUseCase = $lookupServiceReportUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, array $ids, DwsBillingStatus $status): void
    {
        $xs = $this->transaction->run(function () use ($context, $billingId, $ids, $status): Seq {
            $serviceReports = $this->lookupServiceReports($context, $billingId, $ids);
            return $serviceReports->map(
                fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $this->repository->store($x->copy([
                    'status' => $status,
                    'updatedAt' => Carbon::now(),
                ]))
            );
        });
        $this->logger()->info(
            'サービス提供実績記録票が更新されました',
            [
                'id' => implode(',', $xs->map(fn (DwsBillingServiceReport $x): int => $x->id)->toArray()),
            ] + $context->logContext()
        );
    }

    /**
     * サービス提供実績記録票を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param array|int[] $ids
     * @return \ScalikePHP\Seq
     */
    private function lookupServiceReports(Context $context, int $billingId, array $ids): Seq
    {
        $serviceReports = $this->lookupServiceReportUseCase
            ->handle($context, Permission::updateBillings(), ...$ids)
            ->filter(fn (DwsBillingServiceReport $x): bool => $x->dwsBillingId === $billingId);
        if ($serviceReports->size() !== count($ids)) {
            $idList = implode(
                ',',
                $serviceReports
                    ->filter(fn (DwsBillingServiceReport $x): bool => !in_array($x->id, $ids, true))
                    ->toArray()
            );
            throw new NotFoundException("DwsBillingServiceReport({$idList}) not found");
        }
        return $serviceReports;
    }
}

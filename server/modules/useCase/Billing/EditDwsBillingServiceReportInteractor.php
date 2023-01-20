<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * サービス実績記録票編集ユースケース実装.
 */
final class EditDwsBillingServiceReportInteractor implements EditDwsBillingServiceReportUseCase
{
    use Logging;

    private EnsureDwsBillingBundleUseCase $ensureBillingBundleUseCase;
    private LookupDwsBillingServiceReportUseCase $lookupUseCase;
    private DwsBillingServiceReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureBillingBundleUseCase
     * @param \UseCase\Billing\LookupDwsBillingServiceReportUseCase $lookupUseCase
     * @param \Domain\Billing\DwsBillingServiceReportRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureDwsBillingBundleUseCase $ensureBillingBundleUseCase,
        LookupDwsBillingServiceReportUseCase $lookupUseCase,
        DwsBillingServiceReportRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureBillingBundleUseCase = $ensureBillingBundleUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        array $values
    ): DwsBillingServiceReport {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $dwsBillingId, $dwsBillingBundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingServiceReport({$id}) not found.");
            });

        /** @var \Domain\Billing\DwsBillingServiceReport $x */
        $x = $this->transaction->run(fn (): DwsBillingServiceReport => $this->repository->store(
            $entity->copy(
                $values + [
                    'updatedAt' => Carbon::now(),
                ]
            )
        ));
        $this->logger()->info(
            '障害福祉サービス：サービス実績記録票が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}

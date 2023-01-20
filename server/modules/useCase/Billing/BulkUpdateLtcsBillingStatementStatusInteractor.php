<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書 状態一括更新ユースケース実装.
 */
class BulkUpdateLtcsBillingStatementStatusInteractor implements BulkUpdateLtcsBillingStatementStatusUseCase
{
    use Logging;

    private LookupLtcsBillingStatementUseCase $lookupStatementUseCase;
    private LtcsBillingStatementRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Billing\LookupLtcsBillingStatementUseCase $lookupStatementUseCase
     * @param \Domain\Billing\LtcsBillingStatementRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupLtcsBillingStatementUseCase $lookupStatementUseCase,
        LtcsBillingStatementRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupStatementUseCase = $lookupStatementUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, int $bundleId, array $ids, LtcsBillingStatus $status): void
    {
        $this->transaction->run(function () use ($context, $billingId, $bundleId, $ids, $status): void {
            $statements = $this->lookupStatements($context, $billingId, $bundleId, $ids);
            $statements->each(fn (LtcsBillingStatement $x): LtcsBillingStatement => $this->repository->store($x->copy([
                'status' => $status,
                'updatedAt' => Carbon::now(),
            ])));
        });
        $this->logger()->info(
            '介護保険サービス：明細書が更新されました',
            // TODO: IDの複数出力方法はDEV-1577
            ['id' => ''] + $context->logContext()
        );
    }

    /**
     * 介護保険サービス：請求：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param array|int[] $ids
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq
     */
    private function lookupStatements(Context $context, int $billingId, int $bundleId, array $ids): Seq
    {
        $statements = $this->lookupStatementUseCase->handle($context, Permission::updateBillings(), $billingId, $bundleId, ...$ids);
        if ($statements->isEmpty()) {
            $idList = implode(',', $ids);
            throw new NotFoundException("LtcsBillingStatement({$idList}) not found");
        }
        return $statements;
    }
}

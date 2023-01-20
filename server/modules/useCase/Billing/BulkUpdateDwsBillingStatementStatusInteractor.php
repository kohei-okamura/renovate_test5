<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
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
 * 障害福祉サービス：明細書 状態一括更新ユースケース実装.
 */
class BulkUpdateDwsBillingStatementStatusInteractor implements BulkUpdateDwsBillingStatementStatusUseCase
{
    use Logging;

    private SimpleLookupDwsBillingStatementUseCase $lookupStatementUseCase;
    private DwsBillingStatementRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupStatementUseCase
     * @param \Domain\Billing\DwsBillingStatementRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        SimpleLookupDwsBillingStatementUseCase $lookupStatementUseCase,
        DwsBillingStatementRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupStatementUseCase = $lookupStatementUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, array $ids, DwsBillingStatus $status): void
    {
        $this->transaction->run(function () use ($context, $billingId, $ids, $status): void {
            $statements = $this->lookupStatements($context, $billingId, $ids);
            $statements->each(fn (DwsBillingStatement $x): DwsBillingStatement => $this->repository->store($x->copy([
                'status' => $status,
                'updatedAt' => Carbon::now(),
            ])));
        });
        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            // TODO: IDの複数出力方法はDEV-1577
            ['id' => ''] + $context->logContext()
        );
    }

    /**
     * 障害福祉サービス：請求：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param array|int[] $ids
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq
     */
    private function lookupStatements(Context $context, int $billingId, array $ids): Seq
    {
        $statements = $this->lookupStatementUseCase
            ->handle(
                $context,
                Permission::updateBillings(),
                ...$ids
            )->filter(fn (DwsBillingStatement $x): bool => $x->dwsBillingId === $billingId);
        if ($statements->size() !== count($ids)) {
            $idList = implode(
                ',',
                $statements
                    ->filter(fn (DwsBillingStatement $x): bool => !in_array($x->id, $ids, true))
                    ->toArray()
            );
            throw new NotFoundException("DwsBillingStatement({$idList}) not found");
        }
        return $statements;
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingInvoiceFinder;
use Domain\Billing\LtcsBillingInvoiceRepository;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Context\Context;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求書更新ユースケース実装.
 */
final class UpdateLtcsBillingInvoiceListInteractor implements UpdateLtcsBillingInvoiceListUseCase
{
    private BuildLtcsBillingInvoiceListUseCase $buildInvoiceListUseCase;
    private LtcsBillingInvoiceFinder $invoiceFinder;
    private LtcsBillingStatementFinder $statementFinder;
    private LtcsBillingInvoiceRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingInvoiceListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildLtcsBillingInvoiceListUseCase $buildInvoiceListUseCase
     * @param \Domain\Billing\LtcsBillingInvoiceFinder $invoiceFinder
     * @param \Domain\Billing\LtcsBillingStatementFinder $statementFinder
     * @param \Domain\Billing\LtcsBillingInvoiceRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        BuildLtcsBillingInvoiceListUseCase $buildInvoiceListUseCase,
        LtcsBillingInvoiceFinder $invoiceFinder,
        LtcsBillingStatementFinder $statementFinder,
        LtcsBillingInvoiceRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->buildInvoiceListUseCase = $buildInvoiceListUseCase;
        $this->invoiceFinder = $invoiceFinder;
        $this->statementFinder = $statementFinder;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBillingBundle $bundle): Seq
    {
        return $this->transaction->run(fn (): Seq => $this->update($context, $bundle)->computed());
    }

    /**
     * 介護保険サービス：請求書を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function update(Context $context, LtcsBillingBundle $bundle): Seq
    {
        $oldInvoicesMap = $this->findInvoices($bundle)->toMap(function (LtcsBillingInvoice $x): int {
            return $x->defrayerCategory ? $x->defrayerCategory->value() : 0;
        });
        $statements = $this->findStatements($bundle);
        return $this->buildInvoiceListUseCase
            ->handle($context, $bundle, $statements)
            ->map(function (LtcsBillingInvoice $x) use ($oldInvoicesMap): LtcsBillingInvoice {
                $id = $oldInvoicesMap
                    ->get($x->defrayerCategory ? $x->defrayerCategory->value() : 0)
                    ->map(fn (LtcsBillingInvoice $x): int => $x->id)
                    ->orNull();
                return $this->repository->store($x->copy(['id' => $id]));
            });
    }

    /**
     * 請求書の一覧を取得する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function findInvoices(LtcsBillingBundle $bundle): Seq
    {
        $filterParams = ['bundleId' => $bundle->id];
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->invoiceFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 明細書の一覧を取得する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function findStatements(LtcsBillingBundle $bundle): Seq
    {
        $filterParams = ['bundleIds' => [$bundle->id]];
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->statementFinder->find($filterParams, $paginationParams)->list;
    }
}

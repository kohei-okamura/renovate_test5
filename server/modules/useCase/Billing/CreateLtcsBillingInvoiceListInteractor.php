<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle as Bundle;
use Domain\Billing\LtcsBillingInvoice as Invoice;
use Domain\Billing\LtcsBillingInvoiceRepository;
use Domain\Context\Context;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求書一覧生成ユースケース実装.
 */
final class CreateLtcsBillingInvoiceListInteractor implements CreateLtcsBillingInvoiceListUseCase
{
    private BuildLtcsBillingInvoiceListUseCase $buildUseCase;
    private LtcsBillingInvoiceRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoiceListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildLtcsBillingInvoiceListUseCase $buildUseCase
     * @param \Domain\Billing\LtcsBillingInvoiceRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        BuildLtcsBillingInvoiceListUseCase $buildUseCase,
        LtcsBillingInvoiceRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->buildUseCase = $buildUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Bundle $bundle, Seq $statements): Seq
    {
        return $this->transaction->run(fn (): Seq => $this->create($context, $bundle, $statements)->computed());
    }

    /**
     * 介護保険サービス：請求単位を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements
     * @throws \Throwable
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function create(Context $context, Bundle $bundle, Seq $statements): Seq
    {
        $xs = $this->buildUseCase->handle($context, $bundle, $statements);
        return $xs->map(fn (Invoice $x): Invoice => $this->repository->store($x));
    }
}

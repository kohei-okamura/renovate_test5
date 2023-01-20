<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle as Bundle;
use Domain\Billing\DwsBillingInvoice as Invoice;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Context\Context;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求書生成ユースケース実装.
 */
final class CreateDwsBillingInvoiceInteractor implements CreateDwsBillingInvoiceUseCase
{
    private BuildDwsBillingInvoiceUseCase $buildUseCase;
    private DwsBillingInvoiceRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingInvoiceInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingInvoiceUseCase $buildUseCase
     * @param \Domain\Billing\DwsBillingInvoiceRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        BuildDwsBillingInvoiceUseCase $buildUseCase,
        DwsBillingInvoiceRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->buildUseCase = $buildUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Bundle $bundle, Seq $statements): Invoice
    {
        return $this->transaction->run(function () use ($context, $bundle, $statements): Invoice {
            $invoice = $this->buildUseCase->handle($context, $bundle, $statements);
            return $this->repository->store($invoice);
        });
    }
}

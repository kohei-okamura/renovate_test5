<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoice as Invoice;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求書更新ユースケース実装.
 */
class UpdateDwsBillingInvoiceInteractor implements UpdateDwsBillingInvoiceUseCase
{
    private BuildDwsBillingInvoiceUseCase $buildUseCase;
    private DwsBillingStatementFinder $statementFinder;
    private DwsBillingStatementRepository $statementRepository;
    private DwsBillingInvoiceRepository $repository;
    private LookupDwsBillingBundleUseCase $lookupDwsBundleUseCase;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\UpdateDwsBillingInvoiceInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingInvoiceUseCase $buildUseCase
     * @param \Domain\Billing\DwsBillingStatementFinder $statementFinder
     * @param \Domain\Billing\DwsBillingStatementRepository $statementRepository
     * @param \Domain\Billing\DwsBillingInvoiceRepository $repository
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBundleUseCase
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        BuildDwsBillingInvoiceUseCase $buildUseCase,
        DwsBillingStatementFinder $statementFinder,
        DwsBillingStatementRepository $statementRepository,
        DwsBillingInvoiceRepository $repository,
        LookupDwsBillingBundleUseCase $lookupDwsBundleUseCase,
        TransactionManagerFactory $factory
    ) {
        $this->buildUseCase = $buildUseCase;
        $this->statementFinder = $statementFinder;
        $this->statementRepository = $statementRepository;
        $this->repository = $repository;
        $this->lookupDwsBundleUseCase = $lookupDwsBundleUseCase;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, int $billingId, int $bundleId): DwsBillingInvoice
    {
        $statements = $this->getStatements($bundleId);
        $invoiceForUpdate = $this->getInvoice($bundleId);
        $bundle = $this->getDwsBundles($context, $billingId, $bundleId)->head();
        return $this->transaction->run(function () use ($context, $bundle, $statements, $invoiceForUpdate): Invoice {
            $storeInvoice = $this->buildUseCase->handle($context, $bundle, $statements)
                ->copy([
                    'id' => $invoiceForUpdate->id,
                    'updatedAt' => Carbon::now(),
                ]);
            return $this->repository->store($storeInvoice);
        });
    }

    /**
     * 請求書を取得する.
     *
     * @param int $bundleId
     * @return \Domain\Billing\DwsBillingInvoice
     */
    private function getInvoice(int $bundleId): DwsBillingInvoice
    {
        $invoices = $this->repository
            ->lookupByBundleId($bundleId)
            ->values();

        if ($invoices->isEmpty()) {
            throw new NotFoundException("DwsBillingInvoice for DwsBillingBundle ({$bundleId}) not found");
        } else {
            return $invoices->flatten()->head();
        }
    }

    /**
     * 請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @return \ScalikePHP\Seq
     */
    private function getDwsBundles(Context $context, int $billingId, int $bundleId): Seq
    {
        $bundles = $this->lookupDwsBundleUseCase->handle($context, Permission::updateBillings(), $billingId, $bundleId);
        if ($bundles->isEmpty()) {
            throw new NotFoundException("DwsBillingBundle ({$bundleId}) not found");
        } else {
            return $bundles;
        }
    }

    /**
     * 明細書の一覧を取得する.
     *
     * @param int $bundleId
     * @return \ScalikePHP\Seq
     */
    private function getStatements(int $bundleId): Seq
    {
        $statements = $this->statementRepository
            ->lookupByBundleId($bundleId)
            ->values();
        if ($statements->isEmpty()) {
            throw new NotFoundException("DwsBillingStatement for DwsBillingBundle ({$bundleId}) not found");
        } else {
            return $statements->flatten();
        }
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportFinder;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use UseCase\Office\LookupOfficeUseCase;

/**
 * 介護保険サービス：請求生成ユースケース実装.
 */
final class CreateLtcsBillingInteractor implements CreateLtcsBillingUseCase
{
    private LookupOfficeUseCase $lookupOfficeUseCase;
    private CreateLtcsBillingBundleUseCase $createBundleUseCase;
    private CreateLtcsBillingStatementListUseCase $createStatementListUseCase;
    private CreateLtcsBillingInvoiceListUseCase $createInvoiceListUseCase;
    private LtcsBillingRepository $repository;
    private LtcsProvisionReportFinder $provisionReportFinder;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInteractor} constructor.
     *
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\Billing\CreateLtcsBillingBundleUseCase $createBundleUseCase
     * @param \UseCase\Billing\CreateLtcsBillingStatementListUseCase $createStatementListUseCase
     * @param \UseCase\Billing\CreateLtcsBillingInvoiceListUseCase $createInvoiceListUseCase
     * @param \Domain\Billing\LtcsBillingRepository $repository
     * @param \Domain\ProvisionReport\LtcsProvisionReportFinder $provisionReportFinder
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupOfficeUseCase $lookupOfficeUseCase,
        CreateLtcsBillingBundleUseCase $createBundleUseCase,
        CreateLtcsBillingStatementListUseCase $createStatementListUseCase,
        CreateLtcsBillingInvoiceListUseCase $createInvoiceListUseCase,
        LtcsBillingRepository $repository,
        LtcsProvisionReportFinder $provisionReportFinder,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
        $this->createBundleUseCase = $createBundleUseCase;
        $this->createStatementListUseCase = $createStatementListUseCase;
        $this->createInvoiceListUseCase = $createInvoiceListUseCase;
        $this->repository = $repository;
        $this->provisionReportFinder = $provisionReportFinder;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, Carbon $transactedIn, CarbonRange $fixedAt): LtcsBilling
    {
        return $this->transaction->run(function () use ($context, $officeId, $transactedIn, $fixedAt): LtcsBilling {
            $office = $this->lookupOffice($context, $officeId);
            $billing = $this->createBilling($context, $office, $transactedIn);
            foreach ($this->findProvisionReports($office, $fixedAt) as $providedInString => $reports) {
                $providedIn = Carbon::parse($providedInString);
                $bundle = $this->createBundleUseCase->handle($context, $billing, $providedIn, $reports);
                $statements = $this->createStatementListUseCase->handle($context, $office, $bundle, $reports);
                $this->createInvoiceListUseCase->handle($context, $bundle, $statements);
            }
            return $billing;
        });
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @return \Domain\Office\Office
     */
    private function lookupOffice(Context $context, int $officeId): Office
    {
        return $this->lookupOfficeUseCase
            ->handle($context, [Permission::createBillings()], $officeId)
            ->headOption()
            ->getOrElse(function () use ($officeId): void {
                throw new NotFoundException("Office({$officeId}) not found");
            });
    }

    /**
     * 介護保険サービス：請求インスタンスを生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $transactedIn
     * @return \Domain\Billing\LtcsBilling
     */
    private function createBilling(Context $context, Office $office, Carbon $transactedIn): LtcsBilling
    {
        $billing = LtcsBilling::create([
            'organizationId' => $context->organization->id,
            'office' => LtcsBillingOffice::from($office),
            'transactedIn' => $transactedIn,
            'files' => [],
            'status' => LtcsBillingStatus::checking(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
        return $this->repository->store($billing);
    }

    /**
     * 対象となる介護保険サービス：予実の一覧をサービス提供年月ごとに取得する.
     *
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\CarbonRange $fixedAt
     * @return \Domain\ProvisionReport\LtcsProvisionReport[][]|\ScalikePHP\Map
     */
    private function findProvisionReports(Office $office, CarbonRange $fixedAt): Map
    {
        $filterParams = [
            'officeId' => $office->id,
            'fixedAt' => $fixedAt,
            'status' => LtcsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        $xs = $this->provisionReportFinder->find($filterParams, $paginationParams)->list;
        if ($xs->isEmpty()) {
            $start = $fixedAt->start->toDateString();
            $end = $fixedAt->end->toDateString();
            throw new NotFoundException(
                "LtcsProvisionReports that fixed at {$start}〜{$end} not found for Office({$office->id})"
            );
        }
        return $xs->groupBy(fn (LtcsProvisionReport $x): string => $x->providedIn->format('Y-m-01'));
    }
}

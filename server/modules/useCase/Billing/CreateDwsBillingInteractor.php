<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingSource;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\Office\LookupOfficeUseCase;

/**
 * 障害福祉サービス：請求生成ユースケース実装.
 */
final class CreateDwsBillingInteractor implements CreateDwsBillingUseCase
{
    private BuildDwsBillingSourceListUseCase $buildSourceListUseCase;
    private CreateDwsBillingBundleListUseCase $createBundleListUseCase;
    private CreateDwsBillingInvoiceUseCase $createInvoiceUseCase;
    private CreateDwsBillingServiceReportListUseCase $createServiceReportListUseCase;
    private CreateDwsBillingStatementListUseCase $createStatementListUseCase;
    private LookupOfficeUseCase $lookupOfficeUseCase;
    private DwsProvisionReportFinder $provisionReportFinder;
    private DwsBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingSourceListUseCase $buildSourceListUseCase
     * @param \UseCase\Billing\CreateDwsBillingBundleListUseCase $createBundleListUseCase
     * @param \UseCase\Billing\CreateDwsBillingInvoiceUseCase $createInvoiceUseCase
     * @param \UseCase\Billing\CreateDwsBillingServiceReportListUseCase $createServiceReportListUseCase
     * @param \UseCase\Billing\CreateDwsBillingStatementListUseCase $createStatementListUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \Domain\ProvisionReport\DwsProvisionReportFinder $provisionReportFinder
     * @param \Domain\Billing\DwsBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        BuildDwsBillingSourceListUseCase $buildSourceListUseCase,
        CreateDwsBillingBundleListUseCase $createBundleListUseCase,
        CreateDwsBillingInvoiceUseCase $createInvoiceUseCase,
        CreateDwsBillingServiceReportListUseCase $createServiceReportListUseCase,
        CreateDwsBillingStatementListUseCase $createStatementListUseCase,
        LookupOfficeUseCase $lookupOfficeUseCase,
        DwsProvisionReportFinder $provisionReportFinder,
        DwsBillingRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->buildSourceListUseCase = $buildSourceListUseCase;
        $this->createBundleListUseCase = $createBundleListUseCase;
        $this->createInvoiceUseCase = $createInvoiceUseCase;
        $this->createServiceReportListUseCase = $createServiceReportListUseCase;
        $this->createStatementListUseCase = $createStatementListUseCase;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
        $this->provisionReportFinder = $provisionReportFinder;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, Carbon $transactedIn, CarbonRange $fixedAt): DwsBilling
    {
        return $this->transaction->run(function () use ($context, $officeId, $transactedIn, $fixedAt): DwsBilling {
            $office = $this->lookupOffice($context, $officeId);
            $billing = $this->createBilling($context, $office, $transactedIn);

            // 月ごとに処理を行う
            foreach ($this->findProvisionReports($office, $fixedAt) as $providedInString => $provisionReports) {
                $providedIn = Carbon::parse($providedInString);
                $previousProvisionReports = $this->findPreviousProvisionReports($office, $providedIn);
                $sources = $this->buildSourceListUseCase->handle($context, $provisionReports, $previousProvisionReports);
                $bundles = $this->createBundleListUseCase->handle(
                    $context,
                    $office,
                    $billing,
                    $providedIn,
                    $sources
                );

                // 請求単位ごと（＝市町村コードごと）の予実を取得するための Map
                $provisionReportsForBundleMap = $sources
                    ->groupBy(fn (DwsBillingSource $x): string => $x->certification->cityCode)
                    ->mapValues(function (Seq $xs): Seq {
                        return $xs
                            ->map(fn (DwsBillingSource $x): DwsProvisionReport => $x->provisionReport)
                            ->computed();
                    });

                foreach ($bundles as $bundle) {
                    $statements = $this->createStatementListUseCase->handle($context, $office, $bundle);
                    $this->createInvoiceUseCase->handle($context, $bundle, $statements);
                    $this->createServiceReportListUseCase->handle(
                        $context,
                        $bundle,
                        $provisionReportsForBundleMap->get($bundle->cityCode)->toSeq()->flatten(),
                        $previousProvisionReports->computed()
                    );
                }
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
     * 障害福祉サービス：請求インスタンスを生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $transactedIn
     * @return \Domain\Billing\DwsBilling
     */
    private function createBilling(Context $context, Office $office, Carbon $transactedIn): DwsBilling
    {
        $billing = DwsBilling::create([
            'organizationId' => $context->organization->id,
            'office' => DwsBillingOffice::from($office),
            'transactedIn' => $transactedIn,
            'files' => [],
            'status' => DwsBillingStatus::checking(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
        return $this->repository->store($billing);
    }

    /**
     * 対象となる障害福祉サービス：予実の一覧をサービス提供年月ごとに取得する.
     *
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\CarbonRange $fixedAt
     * @return \Domain\ProvisionReport\DwsProvisionReport[][]|\ScalikePHP\Map
     */
    private function findProvisionReports(Office $office, CarbonRange $fixedAt): Map
    {
        $filterParams = [
            'officeId' => $office->id,
            'fixedAt' => $fixedAt,
            'status' => DwsProvisionReportStatus::fixed(),
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
                "DwsProvisionReports that fixed at {$start}〜{$end} not found for Office({$office->id})"
            );
        }
        return $xs->groupBy(fn (DwsProvisionReport $x): string => $x->providedIn->format('Y-m-01'));
    }

    /**
     * 障害福祉サービス：サービス提供年月の前月の予実の一覧を取得する.
     *
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @return \ScalikePHP\Seq
     */
    private function findPreviousProvisionReports(Office $office, Carbon $providedIn): Seq
    {
        $filterParams = [
            'officeId' => $office->id,
            'providedIn' => $providedIn->subMonth(),
            'status' => DwsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];

        return $this->provisionReportFinder
            ->find($filterParams, $paginationParams)
            ->list;
    }
}

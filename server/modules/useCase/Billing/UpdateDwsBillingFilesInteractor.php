<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Arrays;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：ファイル更新ユースケース実装.
 */
final class UpdateDwsBillingFilesInteractor implements UpdateDwsBillingFilesUseCase
{
    use Logging;

    private CreateDwsBillingStatementAndInvoiceCsvUseCase $statementInvoiceUseCase;
    private CreateDwsBillingInvoicePdfUseCase $createInvoicePdfUseCase;
    private CreateDwsBillingServiceReportCsvUseCase $serviceReportUseCase;
    private CreateDwsBillingCopayCoordinationCsvUseCase $copayCoordinationUseCase;
    private CreateDwsBillingCopayCoordinationPdfUseCase $createCopayCoordinationPdfUseCase;
    private CreateDwsBillingServiceReportPdfUseCase $createServiceReportPdfUseCase;
    private LookupDwsBillingUseCase $lookupUseCase;
    private DwsBillingBundleRepository $bundleRepository;
    private DwsBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvUseCase $statementInvoiceUseCase
     * @param \UseCase\Billing\CreateDwsBillingInvoicePdfUseCase $createInvoicePdfUseCase
     * @param \UseCase\Billing\CreateDwsBillingServiceReportPdfUseCase $createServiceReportPdfUseCase
     * @param \UseCase\Billing\CreateDwsBillingServiceReportCsvUseCase $serviceReportUseCase
     * @param \UseCase\Billing\CreateDwsBillingCopayCoordinationCsvUseCase $copayCoordinationUseCase
     * @param \UseCase\Billing\CreateDwsBillingCopayCoordinationPdfUseCase $createCopayCoordinationPdfUseCase
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupUseCase
     * @param \Domain\Billing\DwsBillingBundleRepository $bundleRepository
     * @param \Domain\Billing\DwsBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        CreateDwsBillingStatementAndInvoiceCsvUseCase $statementInvoiceUseCase,
        CreateDwsBillingInvoicePdfUseCase $createInvoicePdfUseCase,
        CreateDwsBillingServiceReportPdfUseCase $createServiceReportPdfUseCase,
        CreateDwsBillingServiceReportCsvUseCase $serviceReportUseCase,
        CreateDwsBillingCopayCoordinationCsvUseCase $copayCoordinationUseCase,
        CreateDwsBillingCopayCoordinationPdfUseCase $createCopayCoordinationPdfUseCase,
        LookupDwsBillingUseCase $lookupUseCase,
        DwsBillingBundleRepository $bundleRepository,
        DwsBillingRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->statementInvoiceUseCase = $statementInvoiceUseCase;
        $this->createInvoicePdfUseCase = $createInvoicePdfUseCase;
        $this->createServiceReportPdfUseCase = $createServiceReportPdfUseCase;
        $this->serviceReportUseCase = $serviceReportUseCase;
        $this->copayCoordinationUseCase = $copayCoordinationUseCase;
        $this->createCopayCoordinationPdfUseCase = $createCopayCoordinationPdfUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->bundleRepository = $bundleRepository;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): DwsBilling
    {
        $billing = $this->lookupBilling($context, $id);
        $groupedBundles = $this->getGroupedBundles($id);
        $files = $groupedBundles->map(function (Seq $bundles) use ($context, $billing) {
            return Arrays::generate(function () use ($context, $billing, $bundles) {
                yield $this->statementInvoiceUseCase->handle($context, $billing, $bundles);
                yield $this->createInvoicePdfUseCase->handle($context, $billing, $bundles);
                yield $this->serviceReportUseCase->handle($context, $billing, $bundles);
                yield $this->createServiceReportPdfUseCase->handle($context, $billing, $bundles);
                $copayCoordinationCsvFile = $this->copayCoordinationUseCase->handle($context, $billing, $bundles);
                if ($copayCoordinationCsvFile->nonEmpty()) {
                    yield $copayCoordinationCsvFile->get();
                    $copayCoordinationPdfFile = $this->createCopayCoordinationPdfUseCase->handle($context, $billing, $bundles);
                    yield $copayCoordinationPdfFile->get();
                }
            });
        })->flatten()->toArray();

        $updatedAt = Carbon::now();

        $overwrites = compact('files', 'updatedAt');

        $x = $this->transaction->run(fn (): DwsBilling => $this->repository->store(
            $billing->copy($overwrites)
        ));

        $this->logger()->info(
            '障害福祉サービス請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 障害福祉サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Billing\DwsBilling
     */
    private function lookupBilling(Context $context, int $id): DwsBilling
    {
        return $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBilling({$id}) not found");
            });
    }

    /**
     * 請求単位の一覧を取得する.
     *
     * @param int $dwsBillingId
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    private function findBundles(int $dwsBillingId): Seq
    {
        return $this->bundleRepository
            ->lookupByBillingId($dwsBillingId)
            ->values()
            ->flatten();
    }

    /**
     * サービス提供年月ごとにグルーピングした請求単位を返す.
     *
     * @param int $dwsBillingId
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    private function getGroupedBundles(int $dwsBillingId): Seq
    {
        return $this->findBundles($dwsBillingId)
            ->groupBy(fn (DwsBillingBundle $x): string => $x->providedIn->toDateString())
            ->values();
    }
}

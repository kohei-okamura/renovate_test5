<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Arrays;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求コピーユースケース実装.
 */
final class CopyDwsBillingInteractor implements CopyDwsBillingUseCase
{
    use Logging;

    private TransactionManager $transaction;

    public function __construct(
        private DwsBillingBundleRepository $bundleRepository,
        private DwsBillingRepository $billingRepository,
        private TransactionManagerFactory $factory,
        private LookupDwsBillingUseCase $lookupBillingUseCase,
        private DwsBillingStatementRepository $statementRepository,
        private DwsBillingServiceReportRepository $serviceReportRepository,
        private DwsBillingCopayCoordinationRepository $copayCoordinationRepository,
        private DwsBillingInvoiceRepository $invoiceRepository,
    ) {
        $this->transaction = $this->factory->factory(
            $this->billingRepository,
            $this->bundleRepository,
            $this->statementRepository,
            $this->serviceReportRepository,
            $this->copayCoordinationRepository,
            $this->invoiceRepository,
        );
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): DwsBilling
    {
        $now = Carbon::now();

        [$old, $new] = $this->transaction->run(function () use ($now, $context, $id): array {
            $billing = $this->lookupBilling($context, $id);
            $newBilling = $this->billingRepository->store($billing->copy([
                'id' => null,
                'files' => [],
                'status' => DwsBillingStatus::ready(),
                'fixedAt' => null,
                'updatedAt' => $now,
            ]));

            $bundles = $this->lookupBundles($id);
            $getNewBundleId = Arrays::generate(function () use ($newBilling, $bundles, $now): iterable {
                foreach ($bundles as $bundle) {
                    $newBundle = $this->bundleRepository->store($bundle->copy([
                        'id' => null,
                        'dwsBillingId' => $newBilling->id,
                        'updatedAt' => $now,
                    ]));
                    yield $bundle->id => $newBundle->id;
                }
            });

            $this->lookupStatementsByBundle($bundles->map(fn (DwsBillingBundle $x): int => $x->id)->toArray())
                ->each(function (Seq $xs, int $bundleId) use ($newBilling, $getNewBundleId, $now): void {
                    $xs->each(fn (DwsBillingStatement $y) => $this->statementRepository->store($y->copy([
                        'id' => null,
                        'dwsBillingId' => $newBilling->id,
                        'dwsBillingBundleId' => $getNewBundleId[$bundleId],
                        'updatedAt' => $now,
                    ])));
                });

            $this->lookupServiceReportsByBundle($bundles->map(fn (DwsBillingBundle $x): int => $x->id)->toArray())
                ->each(function (Seq $xs, int $bundleId) use ($newBilling, $getNewBundleId, $now): void {
                    $xs->each(fn (DwsBillingServiceReport $y) => $this->serviceReportRepository->store($y->copy([
                        'id' => null,
                        'dwsBillingId' => $newBilling->id,
                        'dwsBillingBundleId' => $getNewBundleId[$bundleId],
                        'updatedAt' => $now,
                    ])));
                });

            $this->lookupCopayCoordinationsByBundle($bundles->map(fn (DwsBillingBundle $x): int => $x->id)->toArray())
                ->each(function (Seq $xs, int $bundleId) use ($newBilling, $getNewBundleId, $now): void {
                    $xs->each(fn (DwsBillingCopayCoordination $y) => $this->copayCoordinationRepository->store($y->copy([
                        'id' => null,
                        'dwsBillingId' => $newBilling->id,
                        'dwsBillingBundleId' => $getNewBundleId[$bundleId],
                        'updatedAt' => $now,
                    ])));
                });

            $this->lookupInvoicesByBundle($bundles->map(fn (DwsBillingBundle $x): int => $x->id)->toArray())
                ->each(function (Seq $xs, int $bundleId) use ($getNewBundleId, $now): void {
                    $xs->each(fn (DwsBillingInvoice $y) => $this->invoiceRepository->store($y->copy([
                        'id' => null,
                        'dwsBillingBundleId' => $getNewBundleId[$bundleId],
                        'updatedAt' => $now,
                    ])));
                });

            $x = $this->billingRepository->store($billing->copy([
                'status' => DwsBillingStatus::disabled(),
                'updatedAt' => $now,
            ]));

            return [$x, $newBilling];
        });

        $this->logger()->info(
            '障害福祉サービス：請求が更新されました',
            ['id' => $old->id] + $context->logContext()
        );

        return $new;
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
        return $this->lookupBillingUseCase
            ->handle($context, Permission::createBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBilling({$id}) not found");
            });
    }

    /**
     * 障害福祉サービス：請求単位を取得する.
     *
     * @param int $billingId
     * @return \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq
     */
    private function lookupBundles(int $billingId): Seq
    {
        return $this->bundleRepository
            ->lookupByBillingId($billingId)
            ->values()
            ->flatten();
    }

    /**
     * 障害福祉サービス：請求単位に紐づく障害福祉サービス：明細書を取得する.
     *
     * @param array&int[] $ids
     * @return \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Map
     */
    private function lookupStatementsByBundle(array $ids): Map
    {
        return $this->statementRepository
            ->lookupByBundleId(...$ids);
    }

    /**
     * 障害福祉サービス：請求単位に紐づくサービス提供実績記録票を取得する.
     *
     * @param array&int[] $ids
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Map
     */
    private function lookupServiceReportsByBundle(array $ids): Map
    {
        return $this->serviceReportRepository
            ->lookupByBundleId(...$ids);
    }

    /**
     * 障害福祉サービス：請求単位に紐づく利用者負担上限額管理結果票を取得する.
     *
     * @param array&int[] $ids
     * @return \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Map
     */
    private function lookupCopayCoordinationsByBundle(array $ids): Map
    {
        return $this->copayCoordinationRepository
            ->lookupByBundleId(...$ids);
    }

    /**
     * 障害福祉サービス：請求単位に紐づく障害福祉サービス：請求書を取得する.
     *
     * @param array&int[] $ids
     * @return \Domain\Billing\DwsBillingInvoice[]&\ScalikePHP\Map
     */
    private function lookupInvoicesByBundle(array $ids): Map
    {
        return $this->invoiceRepository
            ->lookupByBundleId(...$ids);
    }
}

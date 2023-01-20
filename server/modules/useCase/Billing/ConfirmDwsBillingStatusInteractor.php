<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：状態確認ユースケース実装.
 */
final class ConfirmDwsBillingStatusInteractor implements ConfirmDwsBillingStatusUseCase
{
    private DwsBillingBundleRepository $bundleRepository;
    private DwsBillingServiceReportRepository $serviceReportRepository;
    private DwsBillingStatementRepository $statementRepository;
    private EditDwsBillingUseCase $editUseCase;

    public function __construct(
        DwsBillingBundleRepository $bundleRepository,
        DwsBillingServiceReportRepository $serviceReportRepository,
        DwsBillingStatementRepository $statementRepository,
        EditDwsBillingUseCase $editUseCase
    ) {
        $this->bundleRepository = $bundleRepository;
        $this->serviceReportRepository = $serviceReportRepository;
        $this->statementRepository = $statementRepository;
        $this->editUseCase = $editUseCase;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, DwsBilling $billing): void
    {
        $bundles = $this->getBundles($billing);
        $statements = $this->getStatements($bundles);
        $serviceReports = $this->getServiceReports($bundles);

        // 明細書が確定済みの場合は、利用者負担上限額管理結果票はすでに確定済みなので見ない
        $canBeReady = $serviceReports->forAll(fn (DwsBillingServiceReport $x): bool => $x->status === DwsBillingStatus::fixed())
            && $statements->forAll(fn (DwsBillingStatement $x): bool => $x->status === DwsBillingStatus::fixed());
        // ジョブの場合、シリアライズによってシングルトンが壊れるので、value() で比較する
        if ($canBeReady && $billing->status->value() === DwsBillingStatus::checking()->value()) {
            $this->editUseCase->handle($context, $billing->id, ['status' => DwsBillingStatus::ready()]);
        } elseif (!$canBeReady && $billing->status->value() === DwsBillingStatus::ready()->value()) {
            $this->editUseCase->handle($context, $billing->id, ['status' => DwsBillingStatus::checking()]);
        }
    }

    /**
     * 請求単位を取得する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    private function getBundles(DwsBilling $billing): Seq
    {
        return $this->bundleRepository->lookupByBillingId($billing->id)->values()->flatten();
    }

    /**
     * 明細書を取得する.
     *
     * @param \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq
     */
    private function getStatements(Seq $bundles): Seq
    {
        return $this->statementRepository
            ->lookupByBundleId(...$bundles->map(fn (DwsBillingBundle $x): int => $x->id))
            ->values()
            ->flatten();
    }

    /**
     * サービス提供実績記録票を取得する.
     *
     * @param \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq
     */
    private function getServiceReports(Seq $bundles): Seq
    {
        return $this->serviceReportRepository
            ->lookupByBundleId(...$bundles->map(fn (DwsBillingBundle $x): int => $x->id))
            ->values()
            ->flatten();
    }
}

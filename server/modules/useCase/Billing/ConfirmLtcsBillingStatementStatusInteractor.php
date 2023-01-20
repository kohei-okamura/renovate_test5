<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求：明細書状態確認ユースケース実装.
 */
class ConfirmLtcsBillingStatementStatusInteractor implements ConfirmLtcsBillingStatementStatusUseCase
{
    private LtcsBillingBundleRepository $bundleRepository;
    private LtcsBillingStatementRepository $statementRepository;
    private EditLtcsBillingUseCase $editUseCase;

    public function __construct(
        LtcsBillingBundleRepository $bundleRepository,
        LtcsBillingStatementRepository $statementRepository,
        EditLtcsBillingUseCase $editUseCase
    ) {
        $this->bundleRepository = $bundleRepository;
        $this->statementRepository = $statementRepository;
        $this->editUseCase = $editUseCase;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, LtcsBilling $billing): void
    {
        $bundles = $this->getBundles($billing);
        $statements = $this->getStatements($bundles);
        if ($statements->forAll(fn (LtcsBillingStatement $x): bool => $x->status === LtcsBillingStatus::fixed())) {
            $this->editUseCase->handle($context, $billing->id, ['status' => LtcsBillingStatus::ready()]);
        }
    }

    /**
     * 請求単位を取得する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @return \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq
     */
    private function getBundles(LtcsBilling $billing): Seq
    {
        return $this->bundleRepository->lookupByBillingId($billing->id)->values()->flatten();
    }

    /**
     * 明細書を取得する.
     *
     * @param \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq
     */
    private function getStatements(Seq $bundles): Seq
    {
        return $this->statementRepository
            ->lookupByBundleId(...$bundles->map(fn (LtcsBillingBundle $x): int => $x->id))
            ->values()
            ->flatten();
    }
}

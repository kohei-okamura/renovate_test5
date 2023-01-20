<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書簡易取得ユースケース実装.
 */
class SimpleLookupLtcsBillingStatementInteractor implements SimpleLookupLtcsBillingStatementUseCase
{
    private EnsureLtcsBillingBundleUseCase $ensureUseCase;
    private LtcsBillingStatementRepository $ltcsBillingStatementRepository;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureLtcsBillingBundleUseCase $ensureUseCase
     * @param \Domain\Billing\LtcsBillingStatementRepository $ltcsBillingStatementRepository
     */
    public function __construct(
        EnsureLtcsBillingBundleUseCase $ensureUseCase,
        LtcsBillingStatementRepository $ltcsBillingStatementRepository
    ) {
        $this->ensureUseCase = $ensureUseCase;
        $this->ltcsBillingStatementRepository = $ltcsBillingStatementRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->ltcsBillingStatementRepository->lookup(...$ids);
        $xs->each(
            fn (LtcsBillingStatement $x) => $this->ensureUseCase->handle($context, $permission, $x->billingId, $x->bundleId)
        );
        return $xs;
    }
}

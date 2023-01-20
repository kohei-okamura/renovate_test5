<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Types;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求単位取得ユースケース実装.
 */
final class LookupLtcsBillingBundleInteractor implements LookupLtcsBillingBundleUseCase
{
    private EnsureLtcsBillingUseCase $ensureUseCase;
    private LtcsBillingBundleRepository $repository;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureLtcsBillingUseCase $ensureUseCase
     * @param \Domain\Billing\LtcsBillingBundleRepository $repository
     */
    public function __construct(
        EnsureLtcsBillingUseCase $ensureUseCase,
        LtcsBillingBundleRepository $repository
    ) {
        $this->ensureUseCase = $ensureUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, $billing, int ...$ids): Seq
    {
        if ($billing instanceof LtcsBilling) {
            $billingId = $billing->id;
        } elseif (is_int($billing)) {
            $this->ensureUseCase->handle($context, $permission, $billing);
            $billingId = $billing;
        } else {
            $expected = 'either int or an instance of ' . LtcsBilling::class;
            $actual = Types::getType($billing);
            throw new InvalidArgumentException("Argument 3 passed to handle() must be {$expected}, {$actual} given");
        }
        $xs = $this->repository->lookup(...$ids);
        $isAccessible = $xs->forAll(fn (LtcsBillingBundle $x): bool => $x->billingId === $billingId);
        return $isAccessible ? $xs : Seq::empty();
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Types;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書取得ユースケース実装.
 */
final class LookupLtcsBillingStatementInteractor implements LookupLtcsBillingStatementUseCase
{
    private EnsureLtcsBillingBundleUseCase $ensureUseCase;
    private LtcsBillingStatementRepository $repository;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureLtcsBillingBundleUseCase $ensureUseCase
     * @param \Domain\Billing\LtcsBillingStatementRepository $repository
     */
    public function __construct(
        EnsureLtcsBillingBundleUseCase $ensureUseCase,
        LtcsBillingStatementRepository $repository
    ) {
        $this->ensureUseCase = $ensureUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, $billing, $bundle, int ...$ids): Seq
    {
        if ($billing instanceof LtcsBilling && $bundle instanceof LtcsBillingBundle) {
            $bundleId = $bundle->id;
        } elseif (is_int($billing) && is_int($bundle)) {
            $this->ensureUseCase->handle($context, $permission, $billing, $bundle);
            $bundleId = $bundle;
        } elseif ($billing instanceof LtcsBilling || is_int($billing)) {
            $expected = 'either int or an instance of ' . LtcsBillingBundle::class;
            $actual = Types::getType($bundle);
            throw new InvalidArgumentException("Argument 4 passed to handle() must be {$expected}, {$actual} given");
        } else {
            $expected = 'either int or an instance of ' . LtcsBilling::class;
            $actual = Types::getType($billing);
            throw new InvalidArgumentException("Argument 3 passed to handle() must be {$expected}, {$actual} given");
        }
        $xs = $this->repository->lookup(...$ids);
        $isAccessible = $xs->forAll(fn (LtcsBillingStatement $x): bool => $x->bundleId === $bundleId);
        return $isAccessible ? $xs : Seq::empty();
    }
}

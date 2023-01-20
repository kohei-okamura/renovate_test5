<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * 介護保険サービス：請求単位保証ユースケース実装.
 */
final class EnsureLtcsBillingBundleInteractor implements EnsureLtcsBillingBundleUseCase
{
    private LookupLtcsBillingBundleUseCase $useCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupLtcsBillingBundleUseCase $useCase
     */
    public function __construct(
        LookupLtcsBillingBundleUseCase $useCase
    ) {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $billingId, int $bundleId): void
    {
        $this->useCase
            ->handle($context, $permission, $billingId, $bundleId)
            ->headOption()
            ->getOrElse(function () use ($bundleId) {
                throw new NotFoundException("LtcsBillingBundle({$bundleId}) not found");
            });
    }
}

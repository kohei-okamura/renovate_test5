<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * 障害福祉サービス：請求単位保証ユースケース実装.
 */
final class EnsureDwsBillingBundleInteractor implements EnsureDwsBillingBundleUseCase
{
    private LookupDwsBillingBundleUseCase $useCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $useCase
     */
    public function __construct(LookupDwsBillingBundleUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $billingId, int $bundleId): void
    {
        $this->useCase
            ->handle($context, $permission, $billingId, $bundleId)
            ->headOption()
            ->getOrElse(function () use ($bundleId) {
                throw new NotFoundException("DwsBillingBundle({$bundleId}) not found");
            });
    }
}

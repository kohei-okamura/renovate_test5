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
 * 介護保険サービス：請求保証ユースケース実装.
 */
final class EnsureLtcsBillingInteractor implements EnsureLtcsBillingUseCase
{
    private LookupLtcsBillingUseCase $useCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $useCase
     */
    public function __construct(LookupLtcsBillingUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $billingId): void
    {
        $this->useCase
            ->handle($context, $permission, $billingId)
            ->headOption()
            ->getOrElse(function () use ($billingId) {
                throw new NotFoundException("LtcsBilling({$billingId}) not found");
            });
    }
}

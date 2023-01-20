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
 * 障害福祉サービス：請求保証ユースケース実装.
 */
final class EnsureDwsBillingInteractor implements EnsureDwsBillingUseCase
{
    private LookupDwsBillingUseCase $useCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Billing\LookupDwsBillingUseCase $useCase
     */
    public function __construct(LookupDwsBillingUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $dwsBillingId): void
    {
        $this->useCase
            ->handle($context, $permission, $dwsBillingId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingId) {
                throw new NotFoundException("DwsBilling({$dwsBillingId}) not found");
            });
    }
}

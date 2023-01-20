<?php

declare(strict_types=1);
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingFinder;
use Domain\Context\Context;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

class FindLtcsBillingInteractor implements FindLtcsBillingUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * constructor.
     *
     * @param \Domain\Billing\LtcsBillingFinder $finder
     */
    public function __construct(LtcsBillingFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return ['organizationId' => $context->organization->id] + $this->getOfficeIdFilter($context, $permission);
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}

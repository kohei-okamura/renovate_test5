<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingFinder;
use Domain\Context\Context;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 障害福祉サービス：請求検索ユースケース.
 */
final class FindDwsBillingInteractor implements FindDwsBillingUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * constructor.
     *
     * @param \Domain\Billing\DwsBillingFinder $finder
     */
    public function __construct(DwsBillingFinder $finder)
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

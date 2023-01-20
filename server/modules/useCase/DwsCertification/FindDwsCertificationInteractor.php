<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Context\Context;
use Domain\DwsCertification\DwsCertificationFinder;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 障害福祉サービス受給者証検索ユースケース実装.
 */
final class FindDwsCertificationInteractor implements FindDwsCertificationUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\DwsCertification\DwsCertificationFinder $finder
     */
    public function __construct(DwsCertificationFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission)
            + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'date';
    }
}

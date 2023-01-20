<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\OfficeFinder;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionsInteractorFeature;

/**
 * 事業所検索ユースケース実装.
 */
final class FindOfficeInteractor implements FindOfficeUseCase
{
    use FindWithPermissionsInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeFinder $finder
     */
    public function __construct(OfficeFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, array $permissions): array
    {
        $key = $context->isAuthorizedTo(Permission::listExternalOffices()) ? 'officeIdsOrExternal' : 'officeIds';
        return $this->getOfficeIdFilter($context, $permissions, $key)
            + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'name';
    }
}

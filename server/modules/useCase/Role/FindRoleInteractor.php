<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\Role\RoleFinder;
use UseCase\FindInteractorFeature;

/**
 * ロール一覧取得ユースケース実装.
 */
final class FindRoleInteractor implements FindRoleUseCase
{
    use FindInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Role\RoleFinder $finder
     */
    public function __construct(RoleFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context): array
    {
        return ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'sortOrder';
    }
}

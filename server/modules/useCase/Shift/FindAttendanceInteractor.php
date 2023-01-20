<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\AttendanceFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 勤務実績検索ユースケース実装.
 */
final class FindAttendanceInteractor implements FindAttendanceUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Shift\AttendanceFinder $finder
     */
    public function __construct(AttendanceFinder $finder)
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
        return 'id';
    }
}

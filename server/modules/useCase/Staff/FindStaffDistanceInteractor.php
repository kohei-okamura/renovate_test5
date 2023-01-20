<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Location;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Staff\StaffDistanceFinder;
use Domain\User\UserRepository;
use Illuminate\Support\Arr;
use Lib\Exceptions\InvalidArgumentException;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * スタッフ距離情報検索ユースケース実装.
 */
final class FindStaffDistanceInteractor implements FindStaffDistanceUseCase
{
    use FindWithPermissionInteractorFeature;

    private UserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param \Domain\User\UserRepository $userRepository
     * @param \Domain\Staff\StaffDistanceFinder $finder
     */
    public function __construct(UserRepository $userRepository, StaffDistanceFinder $finder)
    {
        $this->userRepository = $userRepository;
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, array $filterParams, array $paginationParams): FinderResult
    {
        $sortBy = Arr::get($paginationParams, 'sortBy');
        $filter = $this->defaultFilterParams($context, $permission)
            + ['location' => $this->location($filterParams)]
            + $filterParams;
        return $this->finder->find(
            $filter,
            ['sortBy' => $sortBy ?: $this->defaultSortBy()] + $paginationParams
        );
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
        return 'distance';
    }

    /**
     * 位置情報を生成する.
     *
     * @param array $filterParams
     * @return \Domain\Common\Location
     */
    private function location(array $filterParams): Location
    {
        if (!array_key_exists('location', $filterParams)) {
            throw new InvalidArgumentException('index(location) is necessary');
        }
        return Location::create($filterParams['location']);
    }
}

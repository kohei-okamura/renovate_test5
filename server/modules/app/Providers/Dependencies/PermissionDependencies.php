<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Permission\PermissionGroupFinder;
use Domain\Permission\PermissionGroupRepository;
use Infrastructure\Permission\PermissionGroupFinderEloquentImpl;
use Infrastructure\Permission\PermissionGroupRepositoryCacheImpl;
use Infrastructure\Permission\PermissionGroupRepositoryEloquentImpl;
use Infrastructure\Permission\PermissionGroupRepositoryFallback;
use UseCase\Permission\FindPermissionGroupInteractor;
use UseCase\Permission\FindPermissionGroupUseCase;

/**
 * Permission Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class PermissionDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            FindPermissionGroupUseCase::class => FindPermissionGroupInteractor::class,
            PermissionGroupFinder::class => PermissionGroupFinderEloquentImpl::class,
            PermissionGroupRepository::class => PermissionGroupRepositoryCacheImpl::class,
            PermissionGroupRepositoryFallback::class => PermissionGroupRepositoryEloquentImpl::class,
        ];
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Role\RoleFinder;
use Domain\Role\RoleRepository;
use Infrastructure\Role\RoleFinderEloquentImpl;
use Infrastructure\Role\RoleRepositoryEloquentImpl;
use UseCase\Role\CreateRoleInteractor;
use UseCase\Role\CreateRoleUseCase;
use UseCase\Role\DeleteRoleInteractor;
use UseCase\Role\DeleteRoleUseCase;
use UseCase\Role\EditRoleInteractor;
use UseCase\Role\EditRoleUseCase;
use UseCase\Role\FindRoleInteractor;
use UseCase\Role\FindRoleUseCase;
use UseCase\Role\GetIndexRoleOptionInteractor;
use UseCase\Role\GetIndexRoleOptionUseCase;
use UseCase\Role\LookupRoleInteractor;
use UseCase\Role\LookupRoleUseCase;

/**
 * Role Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class RoleDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateRoleUseCase::class => CreateRoleInteractor::class,
            DeleteRoleUseCase::class => DeleteRoleInteractor::class,
            EditRoleUseCase::class => EditRoleInteractor::class,
            FindRoleUseCase::class => FindRoleInteractor::class,
            GetIndexRoleOptionUseCase::class => GetIndexRoleOptionInteractor::class,
            LookupRoleUseCase::class => LookupRoleInteractor::class,
            RoleFinder::class => RoleFinderEloquentImpl::class,
            RoleRepository::class => RoleRepositoryEloquentImpl::class,
        ];
    }
}

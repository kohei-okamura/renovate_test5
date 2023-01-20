<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Organization\OrganizationFinder;
use Domain\Organization\OrganizationRepository;
use Domain\Organization\OrganizationSettingRepository;
use Infrastructure\Organization\OrganizationFinderEloquentImpl;
use Infrastructure\Organization\OrganizationRepositoryCacheImpl;
use Infrastructure\Organization\OrganizationRepositoryEloquentImpl;
use Infrastructure\Organization\OrganizationRepositoryFallback;
use Infrastructure\Organization\OrganizationSettingRepositoryEloquentImpl;
use UseCase\Organization\CreateOrganizationSettingInteractor;
use UseCase\Organization\CreateOrganizationSettingUseCase;
use UseCase\Organization\EditOrganizationSettingInteractor;
use UseCase\Organization\EditOrganizationSettingUseCase;
use UseCase\Organization\GetAllValidOrganizationInteractor;
use UseCase\Organization\GetAllValidOrganizationUseCase;
use UseCase\Organization\LookupOrganizationByCodeInteractor;
use UseCase\Organization\LookupOrganizationByCodeUseCase;
use UseCase\Organization\LookupOrganizationSettingInteractor;
use UseCase\Organization\LookupOrganizationSettingUseCase;

/**
 * Organization Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class OrganizationDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateOrganizationSettingUseCase::class => CreateOrganizationSettingInteractor::class,
            EditOrganizationSettingUseCase::class => EditOrganizationSettingInteractor::class,
            GetAllValidOrganizationUseCase::class => GetAllValidOrganizationInteractor::class,
            LookupOrganizationByCodeUseCase::class => LookupOrganizationByCodeInteractor::class,
            LookupOrganizationSettingUseCase::class => LookupOrganizationSettingInteractor::class,
            OrganizationFinder::class => OrganizationFinderEloquentImpl::class,
            OrganizationRepository::class => OrganizationRepositoryCacheImpl::class,
            OrganizationRepositoryFallback::class => OrganizationRepositoryEloquentImpl::class,
            OrganizationSettingRepository::class => OrganizationSettingRepositoryEloquentImpl::class,
        ];
    }
}

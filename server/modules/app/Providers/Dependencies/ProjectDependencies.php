<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Project\DwsProjectFinder;
use Domain\Project\DwsProjectRepository;
use Domain\Project\DwsProjectServiceMenuFinder;
use Domain\Project\DwsProjectServiceMenuRepository;
use Domain\Project\LtcsProjectFinder;
use Domain\Project\LtcsProjectRepository;
use Domain\Project\LtcsProjectServiceMenuFinder;
use Domain\Project\LtcsProjectServiceMenuRepository;
use Infrastructure\Project\DwsProjectFinderEloquentImpl;
use Infrastructure\Project\DwsProjectRepositoryEloquentImpl;
use Infrastructure\Project\DwsProjectServiceMenuFinderEloquentImpl;
use Infrastructure\Project\DwsProjectServiceMenuRepositoryEloquentImpl;
use Infrastructure\Project\LtcsProjectFinderEloquentImpl;
use Infrastructure\Project\LtcsProjectRepositoryEloquentImpl;
use Infrastructure\Project\LtcsProjectServiceMenuFinderEloquentImpl;
use Infrastructure\Project\LtcsProjectServiceMenuRepositoryEloquentImpl;
use UseCase\Project\CreateDwsProjectInteractor;
use UseCase\Project\CreateDwsProjectUseCase;
use UseCase\Project\CreateLtcsProjectInteractor;
use UseCase\Project\CreateLtcsProjectUseCase;
use UseCase\Project\DownloadDwsProjectInteractor;
use UseCase\Project\DownloadDwsProjectUseCase;
use UseCase\Project\DownloadLtcsProjectInteractor;
use UseCase\Project\DownloadLtcsProjectUseCase;
use UseCase\Project\EditDwsProjectInteractor;
use UseCase\Project\EditDwsProjectUseCase;
use UseCase\Project\EditLtcsProjectInteractor;
use UseCase\Project\EditLtcsProjectUseCase;
use UseCase\Project\FindDwsProjectInteractor;
use UseCase\Project\FindDwsProjectUseCase;
use UseCase\Project\FindLtcsProjectInteractor;
use UseCase\Project\FindLtcsProjectUseCase;
use UseCase\Project\GetDwsProjectServiceMenuListInteractor;
use UseCase\Project\GetDwsProjectServiceMenuListUseCase;
use UseCase\Project\GetLtcsProjectServiceMenuListInteractor;
use UseCase\Project\GetLtcsProjectServiceMenuListUseCase;
use UseCase\Project\LookupDwsProjectInteractor;
use UseCase\Project\LookupDwsProjectServiceMenuInteractor;
use UseCase\Project\LookupDwsProjectServiceMenuUseCase;
use UseCase\Project\LookupDwsProjectUseCase;
use UseCase\Project\LookupLtcsProjectInteractor;
use UseCase\Project\LookupLtcsProjectServiceMenuInteractor;
use UseCase\Project\LookupLtcsProjectServiceMenuUseCase;
use UseCase\Project\LookupLtcsProjectUseCase;

/**
 * Project Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class ProjectDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateDwsProjectUseCase::class => CreateDwsProjectInteractor::class,
            CreateLtcsProjectUseCase::class => CreateLtcsProjectInteractor::class,
            DownloadDwsProjectUseCase::class => DownloadDwsProjectInteractor::class,
            DownloadLtcsProjectUseCase::class => DownloadLtcsProjectInteractor::class,
            DwsProjectFinder::class => DwsProjectFinderEloquentImpl::class,
            DwsProjectRepository::class => DwsProjectRepositoryEloquentImpl::class,
            DwsProjectServiceMenuFinder::class => DwsProjectServiceMenuFinderEloquentImpl::class,
            DwsProjectServiceMenuRepository::class => DwsProjectServiceMenuRepositoryEloquentImpl::class,
            EditDwsProjectUseCase::class => EditDwsProjectInteractor::class,
            EditLtcsProjectUseCase::class => EditLtcsProjectInteractor::class,
            FindDwsProjectUseCase::class => FindDwsProjectInteractor::class,
            FindLtcsProjectUseCase::class => FindLtcsProjectInteractor::class,
            GetDwsProjectServiceMenuListUseCase::class => GetDwsProjectServiceMenuListInteractor::class,
            GetLtcsProjectServiceMenuListUseCase::class => GetLtcsProjectServiceMenuListInteractor::class,
            LookupDwsProjectServiceMenuUseCase::class => LookupDwsProjectServiceMenuInteractor::class,
            LookupDwsProjectUseCase::class => LookupDwsProjectInteractor::class,
            LookupLtcsProjectServiceMenuUseCase::class => LookupLtcsProjectServiceMenuInteractor::class,
            LookupLtcsProjectUseCase::class => LookupLtcsProjectInteractor::class,
            LtcsProjectFinder::class => LtcsProjectFinderEloquentImpl::class,
            LtcsProjectRepository::class => LtcsProjectRepositoryEloquentImpl::class,
            LtcsProjectServiceMenuFinder::class => LtcsProjectServiceMenuFinderEloquentImpl::class,
            LtcsProjectServiceMenuRepository::class => LtcsProjectServiceMenuRepositoryEloquentImpl::class,
        ];
    }
}

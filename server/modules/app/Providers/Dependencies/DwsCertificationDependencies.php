<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\DwsCertification\DwsCertificationFinder;
use Domain\DwsCertification\DwsCertificationRepository;
use Infrastructure\DwsCertification\DwsCertificationFinderEloquentImpl;
use Infrastructure\DwsCertification\DwsCertificationRepositoryEloquentImpl;
use UseCase\DwsCertification\CreateDwsCertificationInteractor;
use UseCase\DwsCertification\CreateDwsCertificationUseCase;
use UseCase\DwsCertification\DeleteDwsCertificationInteractor;
use UseCase\DwsCertification\DeleteDwsCertificationUseCase;
use UseCase\DwsCertification\EditDwsCertificationInteractor;
use UseCase\DwsCertification\EditDwsCertificationUseCase;
use UseCase\DwsCertification\FindDwsCertificationInteractor;
use UseCase\DwsCertification\FindDwsCertificationUseCase;
use UseCase\DwsCertification\IdentifyDwsCertificationInteractor;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\DwsCertification\LookupDwsCertificationInteractor;
use UseCase\DwsCertification\LookupDwsCertificationUseCase;

/**
 * DwsCertification Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class DwsCertificationDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateDwsCertificationUseCase::class => CreateDwsCertificationInteractor::class,
            DeleteDwsCertificationUseCase::class => DeleteDwsCertificationInteractor::class,
            DwsCertificationFinder::class => DwsCertificationFinderEloquentImpl::class,
            DwsCertificationRepository::class => DwsCertificationRepositoryEloquentImpl::class,
            EditDwsCertificationUseCase::class => EditDwsCertificationInteractor::class,
            FindDwsCertificationUseCase::class => FindDwsCertificationInteractor::class,
            IdentifyDwsCertificationUseCase::class => IdentifyDwsCertificationInteractor::class,
            LookupDwsCertificationUseCase::class => LookupDwsCertificationInteractor::class,
        ];
    }
}

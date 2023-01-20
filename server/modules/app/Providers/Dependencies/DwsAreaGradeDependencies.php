<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\DwsAreaGrade\DwsAreaGradeFeeFinder;
use Domain\DwsAreaGrade\DwsAreaGradeFeeRepository;
use Domain\DwsAreaGrade\DwsAreaGradeFinder;
use Domain\DwsAreaGrade\DwsAreaGradeRepository;
use Infrastructure\DwsAreaGrade\DwsAreaGradeFeeFinderEloquentImpl;
use Infrastructure\DwsAreaGrade\DwsAreaGradeFeeRepositoryEloquentImpl;
use Infrastructure\DwsAreaGrade\DwsAreaGradeFinderEloquentImpl;
use Infrastructure\DwsAreaGrade\DwsAreaGradeRepositoryEloquentImpl;
use UseCase\DwsAreaGrade\FindDwsAreaGradeInteractor;
use UseCase\DwsAreaGrade\FindDwsAreaGradeUseCase;
use UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeInteractor;
use UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase;
use UseCase\Office\LookupDwsAreaGradeInteractor;
use UseCase\Office\LookupDwsAreaGradeUseCase;

/**
 * DwsAreaGrade Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class DwsAreaGradeDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            DwsAreaGradeFinder::class => DwsAreaGradeFinderEloquentImpl::class,
            DwsAreaGradeFeeFinder::class => DwsAreaGradeFeeFinderEloquentImpl::class,
            DwsAreaGradeFeeRepository::class => DwsAreaGradeFeeRepositoryEloquentImpl::class,
            DwsAreaGradeRepository::class => DwsAreaGradeRepositoryEloquentImpl::class,
            FindDwsAreaGradeUseCase::class => FindDwsAreaGradeInteractor::class,
            IdentifyDwsAreaGradeFeeUseCase::class => IdentifyDwsAreaGradeFeeInteractor::class,
            LookupDwsAreaGradeUseCase::class => LookupDwsAreaGradeInteractor::class,
        ];
    }
}

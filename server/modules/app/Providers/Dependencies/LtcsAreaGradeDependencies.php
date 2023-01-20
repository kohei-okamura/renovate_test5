<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\LtcsAreaGrade\LtcsAreaGradeFeeFinder;
use Domain\LtcsAreaGrade\LtcsAreaGradeFeeRepository;
use Domain\LtcsAreaGrade\LtcsAreaGradeFinder;
use Domain\LtcsAreaGrade\LtcsAreaGradeRepository;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeFeeFinderEloquentImpl;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeFeeRepositoryEloquentImpl;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeFinderEloquentImpl;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeRepositoryEloquentImpl;
use UseCase\LtcsAreaGrade\FindLtcsAreaGradeInteractor;
use UseCase\LtcsAreaGrade\FindLtcsAreaGradeUseCase;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeInteractor;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase;
use UseCase\Office\LookupLtcsAreaGradeInteractor;
use UseCase\Office\LookupLtcsAreaGradeUseCase;

/**
 * LtcsAreaGrade Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class LtcsAreaGradeDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            FindLtcsAreaGradeUseCase::class => FindLtcsAreaGradeInteractor::class,
            IdentifyLtcsAreaGradeFeeUseCase::class => IdentifyLtcsAreaGradeFeeInteractor::class,
            LtcsAreaGradeFinder::class => LtcsAreaGradeFinderEloquentImpl::class,
            LtcsAreaGradeFeeFinder::class => LtcsAreaGradeFeeFinderEloquentImpl::class,
            LtcsAreaGradeFeeRepository::class => LtcsAreaGradeFeeRepositoryEloquentImpl::class,
            LtcsAreaGradeRepository::class => LtcsAreaGradeRepositoryEloquentImpl::class,
            LookupLtcsAreaGradeUseCase::class => LookupLtcsAreaGradeInteractor::class,
        ];
    }
}

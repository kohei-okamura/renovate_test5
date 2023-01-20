<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Job\JobRepository;
use Infrastructure\Job\JobRepositoryEloquentImpl;
use UseCase\Job\CreateJobInteractor;
use UseCase\Job\CreateJobUseCase;
use UseCase\Job\CreateJobWithFileInteractor;
use UseCase\Job\CreateJobWithFileUseCase;
use UseCase\Job\EditJobInteractor;
use UseCase\Job\EditJobUseCase;
use UseCase\Job\EndJobInteractor;
use UseCase\Job\EndJobUseCase;
use UseCase\Job\LookupJobByTokenInteractor;
use UseCase\Job\LookupJobByTokenUseCase;
use UseCase\Job\LookupJobInteractor;
use UseCase\Job\LookupJobUseCase;
use UseCase\Job\RunJobInteractor;
use UseCase\Job\RunJobUseCase;
use UseCase\Job\StartJobInteractor;
use UseCase\Job\StartJobUseCase;

/**
 * Job Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class JobDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateJobUseCase::class => CreateJobInteractor::class,
            CreateJobWithFileUseCase::class => CreateJobWithFileInteractor::class,
            EditJobUseCase::class => EditJobInteractor::class,
            EndJobUseCase::class => EndJobInteractor::class,
            JobRepository::class => JobRepositoryEloquentImpl::class,
            LookupJobUseCase::class => LookupJobInteractor::class,
            LookupJobByTokenUseCase::class => LookupJobByTokenInteractor::class,
            RunJobUseCase::class => RunJobInteractor::class,
            StartJobUseCase::class => StartJobInteractor::class,
        ];
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\User\UserDwsCalcSpecFinder;
use Domain\User\UserDwsCalcSpecRepository;
use Domain\User\UserDwsSubsidyFinder;
use Domain\User\UserDwsSubsidyRepository;
use Domain\User\UserFinder;
use Domain\User\UserLtcsCalcSpecFinder;
use Domain\User\UserLtcsCalcSpecRepository;
use Domain\User\UserLtcsSubsidyFinder;
use Domain\User\UserLtcsSubsidyRepository;
use Domain\User\UserRepository;
use Infrastructure\User\UserDwsCalcSpecFinderEloquentImpl;
use Infrastructure\User\UserDwsCalcSpecRepositoryEloquentImpl;
use Infrastructure\User\UserDwsSubsidyFinderEloquentImpl;
use Infrastructure\User\UserDwsSubsidyRepositoryEloquentImpl;
use Infrastructure\User\UserFinderEloquentImpl;
use Infrastructure\User\UserLtcsCalcSpecFinderEloquentImpl;
use Infrastructure\User\UserLtcsCalcSpecRepositoryEloquentImpl;
use Infrastructure\User\UserLtcsSubsidyFinderEloquentImpl;
use Infrastructure\User\UserLtcsSubsidyRepositoryEloquentImpl;
use Infrastructure\User\UserRepositoryEloquentImpl;
use UseCase\User\CreateUserDwsCalcSpecInteractor;
use UseCase\User\CreateUserDwsCalcSpecUseCase;
use UseCase\User\CreateUserDwsSubsidyInteractor;
use UseCase\User\CreateUserDwsSubsidyUseCase;
use UseCase\User\CreateUserInteractor;
use UseCase\User\CreateUserLtcsCalcSpecInteractor;
use UseCase\User\CreateUserLtcsCalcSpecUseCase;
use UseCase\User\CreateUserLtcsSubsidyInteractor;
use UseCase\User\CreateUserLtcsSubsidyUseCase;
use UseCase\User\CreateUserUseCase;
use UseCase\User\DeleteUserLtcsSubsidyInteractor;
use UseCase\User\DeleteUserLtcsSubsidyUseCase;
use UseCase\User\EditUserDwsCalcSpecInteractor;
use UseCase\User\EditUserDwsCalcSpecUseCase;
use UseCase\User\EditUserDwsSubsidyInteractor;
use UseCase\User\EditUserDwsSubsidyUseCase;
use UseCase\User\EditUserInteractor;
use UseCase\User\EditUserLtcsCalcSpecInteractor;
use UseCase\User\EditUserLtcsCalcSpecUseCase;
use UseCase\User\EditUserLtcsSubsidyInteractor;
use UseCase\User\EditUserLtcsSubsidyUseCase;
use UseCase\User\EditUserUseCase;
use UseCase\User\EnsureUserInteractor;
use UseCase\User\EnsureUserUseCase;
use UseCase\User\FindUserDwsCalcSpecInteractor;
use UseCase\User\FindUserDwsCalcSpecUseCase;
use UseCase\User\FindUserDwsSubsidyInteractor;
use UseCase\User\FindUserDwsSubsidyUseCase;
use UseCase\User\FindUserInteractor;
use UseCase\User\FindUserLtcsCalcSpecInteractor;
use UseCase\User\FindUserLtcsCalcSpecUseCase;
use UseCase\User\FindUserLtcsSubsidyInteractor;
use UseCase\User\FindUserLtcsSubsidyUseCase;
use UseCase\User\FindUserUseCase;
use UseCase\User\GetIndexUserOptionInteractor;
use UseCase\User\GetIndexUserOptionUseCase;
use UseCase\User\GetUserInfoInteractor;
use UseCase\User\GetUserInfoUseCase;
use UseCase\User\IdentifyUserDwsCalcSpecInteractor;
use UseCase\User\IdentifyUserDwsCalcSpecUseCase;
use UseCase\User\IdentifyUserDwsSubsidyInteractor;
use UseCase\User\IdentifyUserDwsSubsidyUseCase;
use UseCase\User\IdentifyUserLtcsCalcSpecInteractor;
use UseCase\User\IdentifyUserLtcsCalcSpecUseCase;
use UseCase\User\IdentifyUserLtcsSubsidyInteractor;
use UseCase\User\IdentifyUserLtcsSubsidyUseCase;
use UseCase\User\ImportUserInteractor;
use UseCase\User\ImportUserUseCase;
use UseCase\User\LookupUserDwsCalcSpecInteractor;
use UseCase\User\LookupUserDwsCalcSpecUseCase;
use UseCase\User\LookupUserDwsSubsidyInteractor;
use UseCase\User\LookupUserDwsSubsidyUseCase;
use UseCase\User\LookupUserInteractor;
use UseCase\User\LookupUserLtcsCalcSpecInteractor;
use UseCase\User\LookupUserLtcsCalcSpecUseCase;
use UseCase\User\LookupUserLtcsSubsidyInteractor;
use UseCase\User\LookupUserLtcsSubsidyUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * User Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class UserDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateUserDwsCalcSpecUseCase::class => CreateUserDwsCalcSpecInteractor::class,
            CreateUserDwsSubsidyUseCase::class => CreateUserDwsSubsidyInteractor::class,
            CreateUserLtcsCalcSpecUseCase::class => CreateUserLtcsCalcSpecInteractor::class,
            CreateUserLtcsSubsidyUseCase::class => CreateUserLtcsSubsidyInteractor::class,
            CreateUserUseCase::class => CreateUserInteractor::class,
            DeleteUserLtcsSubsidyUseCase::class => DeleteUserLtcsSubsidyInteractor::class,
            EditUserDwsCalcSpecUseCase::class => EditUserDwsCalcSpecInteractor::class,
            EditUserDwsSubsidyUseCase::class => EditUserDwsSubsidyInteractor::class,
            EditUserLtcsCalcSpecUseCase::class => EditUserLtcsCalcSpecInteractor::class,
            EditUserLtcsSubsidyUseCase::class => EditUserLtcsSubsidyInteractor::class,
            EditUserUseCase::class => EditUserInteractor::class,
            EnsureUserUseCase::class => EnsureUserInteractor::class,
            FindUserDwsCalcSpecUseCase::class => FindUserDwsCalcSpecInteractor::class,
            FindUserDwsSubsidyUseCase::class => FindUserDwsSubsidyInteractor::class,
            FindUserLtcsCalcSpecUseCase::class => FindUserLtcsCalcSpecInteractor::class,
            FindUserLtcsSubsidyUseCase::class => FindUserLtcsSubsidyInteractor::class,
            FindUserUseCase::class => FindUserInteractor::class,
            GetIndexUserOptionUseCase::class => GetIndexUserOptionInteractor::class,
            GetUserInfoUseCase::class => GetUserInfoInteractor::class,
            IdentifyUserDwsCalcSpecUseCase::class => IdentifyUserDwsCalcSpecInteractor::class,
            IdentifyUserDwsSubsidyUseCase::class => IdentifyUserDwsSubsidyInteractor::class,
            IdentifyUserLtcsCalcSpecUseCase::class => IdentifyUserLtcsCalcSpecInteractor::class,
            IdentifyUserLtcsSubsidyUseCase::class => IdentifyUserLtcsSubsidyInteractor::class,
            ImportUserUseCase::class => ImportUserInteractor::class,
            LookupUserDwsCalcSpecUseCase::class => LookupUserDwsCalcSpecInteractor::class,
            LookupUserDwsSubsidyUseCase::class => LookupUserDwsSubsidyInteractor::class,
            LookupUserLtcsCalcSpecUseCase::class => LookupUserLtcsCalcSpecInteractor::class,
            LookupUserLtcsSubsidyUseCase::class => LookupUserLtcsSubsidyInteractor::class,
            LookupUserUseCase::class => LookupUserInteractor::class,
            UserDwsCalcSpecFinder::class => UserDwsCalcSpecFinderEloquentImpl::class,
            UserDwsCalcSpecRepository::class => UserDwsCalcSpecRepositoryEloquentImpl::class,
            UserDwsSubsidyFinder::class => UserDwsSubsidyFinderEloquentImpl::class,
            UserDwsSubsidyRepository::class => UserDwsSubsidyRepositoryEloquentImpl::class,
            UserFinder::class => UserFinderEloquentImpl::class,
            UserLtcsCalcSpecFinder::class => UserLtcsCalcSpecFinderEloquentImpl::class,
            UserLtcsCalcSpecRepository::class => UserLtcsCalcSpecRepositoryEloquentImpl::class,
            UserLtcsSubsidyFinder::class => UserLtcsSubsidyFinderEloquentImpl::class,
            UserLtcsSubsidyRepository::class => UserLtcsSubsidyRepositoryEloquentImpl::class,
            UserRepository::class => UserRepositoryEloquentImpl::class,
        ];
    }
}

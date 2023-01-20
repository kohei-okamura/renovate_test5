<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Staff\InvitationRepository;
use Domain\Staff\StaffDistanceFinder;
use Domain\Staff\StaffEmailVerificationRepository;
use Domain\Staff\StaffFinder;
use Domain\Staff\StaffPasswordResetRepository;
use Domain\Staff\StaffRememberTokenRepository;
use Domain\Staff\StaffRepository;
use Infrastructure\Staff\InvitationRepositoryEloquentImpl;
use Infrastructure\Staff\StaffDistanceFinderEloquentImpl;
use Infrastructure\Staff\StaffEmailVerificationRepositoryEloquentImpl;
use Infrastructure\Staff\StaffFinderEloquentImpl;
use Infrastructure\Staff\StaffPasswordResetRepositoryEloquentImpl;
use Infrastructure\Staff\StaffRememberTokenRepositoryEloquentImpl;
use Infrastructure\Staff\StaffRepositoryEloquentImpl;
use UseCase\Staff\AggregatePermissionCodeListInteractor;
use UseCase\Staff\AggregatePermissionCodeListUseCase;
use UseCase\Staff\AuthenticateStaffInteractor;
use UseCase\Staff\AuthenticateStaffUseCase;
use UseCase\Staff\BuildAuthResponseInteractor;
use UseCase\Staff\BuildAuthResponseUseCase;
use UseCase\Staff\CreateInvitationInteractor;
use UseCase\Staff\CreateInvitationUseCase;
use UseCase\Staff\CreateStaffInteractor;
use UseCase\Staff\CreateStaffPasswordResetInteractor;
use UseCase\Staff\CreateStaffPasswordResetUseCase;
use UseCase\Staff\CreateStaffRememberTokenInteractor;
use UseCase\Staff\CreateStaffRememberTokenUseCase;
use UseCase\Staff\CreateStaffUseCase;
use UseCase\Staff\CreateStaffWithInvitationInteractor;
use UseCase\Staff\CreateStaffWithInvitationUseCase;
use UseCase\Staff\EditInvitationInteractor;
use UseCase\Staff\EditInvitationUseCase;
use UseCase\Staff\EditStaffInteractor;
use UseCase\Staff\EditStaffUseCase;
use UseCase\Staff\FindStaffDistanceInteractor;
use UseCase\Staff\FindStaffDistanceUseCase;
use UseCase\Staff\FindStaffInteractor;
use UseCase\Staff\FindStaffUseCase;
use UseCase\Staff\GetIndexStaffOptionInteractor;
use UseCase\Staff\GetIndexStaffOptionUseCase;
use UseCase\Staff\GetSessionInfoInteractor;
use UseCase\Staff\GetSessionInfoUseCase;
use UseCase\Staff\GetStaffEmailVerificationInteractor;
use UseCase\Staff\GetStaffEmailVerificationUseCase;
use UseCase\Staff\GetStaffInfoInteractor;
use UseCase\Staff\GetStaffInfoUseCase;
use UseCase\Staff\GetStaffPasswordResetInteractor;
use UseCase\Staff\GetStaffPasswordResetUseCase;
use UseCase\Staff\IdentifyStaffByEmailInteractor;
use UseCase\Staff\IdentifyStaffByEmailUseCase;
use UseCase\Staff\LookupInvitationByTokenInteractor;
use UseCase\Staff\LookupInvitationByTokenUseCase;
use UseCase\Staff\LookupInvitationInteractor;
use UseCase\Staff\LookupInvitationUseCase;
use UseCase\Staff\LookupStaffInteractor;
use UseCase\Staff\LookupStaffRememberTokenInteractor;
use UseCase\Staff\LookupStaffRememberTokenUseCase;
use UseCase\Staff\LookupStaffUseCase;
use UseCase\Staff\RemoveStaffRememberTokenInteractor;
use UseCase\Staff\RemoveStaffRememberTokenUseCase;
use UseCase\Staff\ResetStaffPasswordInteractor;
use UseCase\Staff\ResetStaffPasswordUseCase;
use UseCase\Staff\StaffLoggedOutInteractor;
use UseCase\Staff\StaffLoggedOutUseCase;
use UseCase\Staff\VerifyStaffEmailInteractor;
use UseCase\Staff\VerifyStaffEmailUseCase;

/**
 * Staff Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class StaffDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            AggregatePermissionCodeListUseCase::class => AggregatePermissionCodeListInteractor::class,
            AuthenticateStaffUseCase::class => AuthenticateStaffInteractor::class,
            BuildAuthResponseUseCase::class => BuildAuthResponseInteractor::class,
            CreateInvitationUseCase::class => CreateInvitationInteractor::class,
            CreateStaffPasswordResetUseCase::class => CreateStaffPasswordResetInteractor::class,
            CreateStaffRememberTokenUseCase::class => CreateStaffRememberTokenInteractor::class,
            CreateStaffUseCase::class => CreateStaffInteractor::class,
            CreateStaffWithInvitationUseCase::class => CreateStaffWithInvitationInteractor::class,
            EditInvitationUseCase::class => EditInvitationInteractor::class,
            EditStaffUseCase::class => EditStaffInteractor::class,
            FindStaffDistanceUseCase::class => FindStaffDistanceInteractor::class,
            FindStaffUseCase::class => FindStaffInteractor::class,
            GetIndexStaffOptionUseCase::class => GetIndexStaffOptionInteractor::class,
            GetSessionInfoUseCase::class => GetSessionInfoInteractor::class,
            GetStaffEmailVerificationUseCase::class => GetStaffEmailVerificationInteractor::class,
            GetStaffInfoUseCase::class => GetStaffInfoInteractor::class,
            GetStaffPasswordResetUseCase::class => GetStaffPasswordResetInteractor::class,
            InvitationRepository::class => InvitationRepositoryEloquentImpl::class,
            LookupInvitationByTokenUseCase::class => LookupInvitationByTokenInteractor::class,
            LookupInvitationUseCase::class => LookupInvitationInteractor::class,
            IdentifyStaffByEmailUseCase::class => IdentifyStaffByEmailInteractor::class,
            LookupStaffRememberTokenUseCase::class => LookupStaffRememberTokenInteractor::class,
            LookupStaffUseCase::class => LookupStaffInteractor::class,
            RemoveStaffRememberTokenUseCase::class => RemoveStaffRememberTokenInteractor::class,
            ResetStaffPasswordUseCase::class => ResetStaffPasswordInteractor::class,
            StaffDistanceFinder::class => StaffDistanceFinderEloquentImpl::class,
            StaffEmailVerificationRepository::class => StaffEmailVerificationRepositoryEloquentImpl::class,
            StaffFinder::class => StaffFinderEloquentImpl::class,
            StaffLoggedOutUseCase::class => StaffLoggedOutInteractor::class,
            StaffPasswordResetRepository::class => StaffPasswordResetRepositoryEloquentImpl::class,
            StaffRememberTokenRepository::class => StaffRememberTokenRepositoryEloquentImpl::class,
            StaffRepository::class => StaffRepositoryEloquentImpl::class,
            VerifyStaffEmailUseCase::class => VerifyStaffEmailInteractor::class,
        ];
    }
}

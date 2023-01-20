<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Shift\AttendanceFinder;
use Domain\Shift\AttendanceRepository;
use Domain\Shift\ShiftFinder;
use Domain\Shift\ShiftRepository;
use Infrastructure\Shift\AttendanceFinderEloquentImpl;
use Infrastructure\Shift\AttendanceRepositoryEloquentImpl;
use Infrastructure\Shift\ShiftFinderEloquentImpl;
use Infrastructure\Shift\ShiftRepositoryEloquentImpl;
use UseCase\Shift\BulkCreateAttendanceInteractor;
use UseCase\Shift\BulkCreateAttendanceUseCase;
use UseCase\Shift\CancelAttendanceInteractor;
use UseCase\Shift\CancelAttendanceUseCase;
use UseCase\Shift\CancelShiftInteractor;
use UseCase\Shift\CancelShiftUseCase;
use UseCase\Shift\ConfirmAttendanceInteractor;
use UseCase\Shift\ConfirmAttendanceUseCase;
use UseCase\Shift\ConfirmShiftInteractor;
use UseCase\Shift\ConfirmShiftUseCase;
use UseCase\Shift\CreateAttendanceInteractor;
use UseCase\Shift\CreateAttendanceUseCase;
use UseCase\Shift\CreateShiftInteractor;
use UseCase\Shift\CreateShiftUseCase;
use UseCase\Shift\EditAttendanceInteractor;
use UseCase\Shift\EditAttendanceUseCase;
use UseCase\Shift\EditShiftInteractor;
use UseCase\Shift\EditShiftUseCase;
use UseCase\Shift\FindAttendanceInteractor;
use UseCase\Shift\FindAttendanceUseCase;
use UseCase\Shift\FindShiftInteractor;
use UseCase\Shift\FindShiftUseCase;
use UseCase\Shift\GenerateShiftTemplateInteractor;
use UseCase\Shift\GenerateShiftTemplateUseCase;
use UseCase\Shift\ImportShiftInteractor;
use UseCase\Shift\ImportShiftUseCase;
use UseCase\Shift\LoadShiftInteractor;
use UseCase\Shift\LoadShiftUseCase;
use UseCase\Shift\LookupAttendanceInteractor;
use UseCase\Shift\LookupAttendanceUseCase;
use UseCase\Shift\LookupShiftInteractor;
use UseCase\Shift\LookupShiftUseCase;
use UseCase\Shift\RunCancelAttendanceJobInteractor;
use UseCase\Shift\RunCancelAttendanceJobUseCase;
use UseCase\Shift\RunCancelShiftJobInteractor;
use UseCase\Shift\RunCancelShiftJobUseCase;
use UseCase\Shift\RunConfirmAttendanceJobInteractor;
use UseCase\Shift\RunConfirmAttendanceJobUseCase;
use UseCase\Shift\RunConfirmShiftJobInteractor;
use UseCase\Shift\RunConfirmShiftJobUseCase;
use UseCase\Shift\RunCreateShiftTemplateJobInteractor;
use UseCase\Shift\RunCreateShiftTemplateJobUseCase;
use UseCase\Shift\RunImportShiftJobInteractor;
use UseCase\Shift\RunImportShiftJobUseCase;

/**
 * Shift Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class ShiftDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            AttendanceFinder::class => AttendanceFinderEloquentImpl::class,
            AttendanceRepository::class => AttendanceRepositoryEloquentImpl::class,
            BulkCreateAttendanceUseCase::class => BulkCreateAttendanceInteractor::class,
            CancelAttendanceUseCase::class => CancelAttendanceInteractor::class,
            CancelShiftUseCase::class => CancelShiftInteractor::class,
            ConfirmAttendanceUseCase::class => ConfirmAttendanceInteractor::class,
            ConfirmShiftUseCase::class => ConfirmShiftInteractor::class,
            CreateAttendanceUseCase::class => CreateAttendanceInteractor::class,
            CreateShiftUseCase::class => CreateShiftInteractor::class,
            EditAttendanceUseCase::class => EditAttendanceInteractor::class,
            EditShiftUseCase::class => EditShiftInteractor::class,
            FindAttendanceUseCase::class => FindAttendanceInteractor::class,
            FindShiftUseCase::class => FindShiftInteractor::class,
            GenerateShiftTemplateUseCase::class => GenerateShiftTemplateInteractor::class,
            ImportShiftUseCase::class => ImportShiftInteractor::class,
            LoadShiftUseCase::class => LoadShiftInteractor::class,
            LookupAttendanceUseCase::class => LookupAttendanceInteractor::class,
            LookupShiftUseCase::class => LookupShiftInteractor::class,
            RunCancelAttendanceJobUseCase::class => RunCancelAttendanceJobInteractor::class,
            RunCancelShiftJobUseCase::class => RunCancelShiftJobInteractor::class,
            RunConfirmAttendanceJobUseCase::class => RunConfirmAttendanceJobInteractor::class,
            RunConfirmShiftJobUseCase::class => RunConfirmShiftJobInteractor::class,
            RunCreateShiftTemplateJobUseCase::class => RunCreateShiftTemplateJobInteractor::class,
            RunImportShiftJobUseCase::class => RunImportShiftJobInteractor::class,
            ShiftFinder::class => ShiftFinderEloquentImpl::class,
            ShiftRepository::class => ShiftRepositoryEloquentImpl::class,
        ];
    }
}

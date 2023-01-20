<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use App\Http\Requests\Delegates\LtcsProvisionReportFormDelegate;
use App\Http\Requests\Delegates\LtcsProvisionReportFormDelegateImpl;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\LtcsProvisionReportFinder;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Infrastructure\ProvisionReport\DwsProvisionReportFinderEloquentImpl;
use Infrastructure\ProvisionReport\DwsProvisionReportRepositoryEloquentImpl;
use Infrastructure\ProvisionReport\LtcsProvisionReportFinderEloquentImpl;
use Infrastructure\ProvisionReport\LtcsProvisionReportRepositoryEloquentImpl;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixInteractor;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamInteractor;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamInteractor;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase;
use UseCase\ProvisionReport\DeleteDwsProvisionReportInteractor;
use UseCase\ProvisionReport\DeleteDwsProvisionReportUseCase;
use UseCase\ProvisionReport\DeleteLtcsProvisionReportInteractor;
use UseCase\ProvisionReport\DeleteLtcsProvisionReportUseCase;
use UseCase\ProvisionReport\FindDwsProvisionReportInteractor;
use UseCase\ProvisionReport\FindDwsProvisionReportUseCase;
use UseCase\ProvisionReport\FindLtcsProvisionReportInteractor;
use UseCase\ProvisionReport\FindLtcsProvisionReportUseCase;
use UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfInteractor;
use UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfUseCase;
use UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfInteractor;
use UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportInteractor;
use UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryInteractor;
use UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;
use UseCase\ProvisionReport\GetIndexDwsProvisionReportDigestInteractor;
use UseCase\ProvisionReport\GetIndexDwsProvisionReportDigestUseCase;
use UseCase\ProvisionReport\GetIndexLtcsProvisionReportDigestInteractor;
use UseCase\ProvisionReport\GetIndexLtcsProvisionReportDigestUseCase;
use UseCase\ProvisionReport\GetLtcsProvisionReportInteractor;
use UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryInteractor;
use UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase;
use UseCase\ProvisionReport\GetLtcsProvisionReportUseCase;
use UseCase\ProvisionReport\IdentifyDwsProvisionReportInteractor;
use UseCase\ProvisionReport\IdentifyDwsProvisionReportUseCase;
use UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobInteractor;
use UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase;
use UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobInteractor;
use UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase;
use UseCase\ProvisionReport\UpdateDwsProvisionReportInteractor;
use UseCase\ProvisionReport\UpdateDwsProvisionReportStatusInteractor;
use UseCase\ProvisionReport\UpdateDwsProvisionReportStatusUseCase;
use UseCase\ProvisionReport\UpdateDwsProvisionReportUseCase;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportInteractor;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusInteractor;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusUseCase;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportUseCase;

/**
 * ProvisionReport Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class ProvisionReportDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            BuildLtcsProvisionReportSheetAppendixPdfParamUseCase::class => BuildLtcsProvisionReportSheetAppendixPdfParamInteractor::class,
            BuildLtcsProvisionReportSheetAppendixUseCase::class => BuildLtcsProvisionReportSheetAppendixInteractor::class,
            BuildLtcsProvisionReportSheetPdfParamUseCase::class => BuildLtcsProvisionReportSheetPdfParamInteractor::class,
            DeleteDwsProvisionReportUseCase::class => DeleteDwsProvisionReportInteractor::class,
            DeleteLtcsProvisionReportUseCase::class => DeleteLtcsProvisionReportInteractor::class,
            DwsProvisionReportFinder::class => DwsProvisionReportFinderEloquentImpl::class,
            DwsProvisionReportRepository::class => DwsProvisionReportRepositoryEloquentImpl::class,
            FindDwsProvisionReportUseCase::class => FindDwsProvisionReportInteractor::class,
            FindLtcsProvisionReportUseCase::class => FindLtcsProvisionReportInteractor::class,
            GenerateDwsServiceReportPreviewPdfUseCase::class => GenerateDwsServiceReportPreviewPdfInteractor::class,
            GenerateLtcsProvisionReportSheetPdfUseCase::class => GenerateLtcsProvisionReportSheetPdfInteractor::class,
            GetDwsProvisionReportTimeSummaryUseCase::class => GetDwsProvisionReportTimeSummaryInteractor::class,
            GetDwsProvisionReportUseCase::class => GetDwsProvisionReportInteractor::class,
            GetIndexDwsProvisionReportDigestUseCase::class => GetIndexDwsProvisionReportDigestInteractor::class,
            GetIndexLtcsProvisionReportDigestUseCase::class => GetIndexLtcsProvisionReportDigestInteractor::class,
            GetLtcsProvisionReportScoreSummaryUseCase::class => GetLtcsProvisionReportScoreSummaryInteractor::class,
            GetLtcsProvisionReportUseCase::class => GetLtcsProvisionReportInteractor::class,
            IdentifyDwsProvisionReportUseCase::class => IdentifyDwsProvisionReportInteractor::class,
            LtcsProvisionReportFinder::class => LtcsProvisionReportFinderEloquentImpl::class,
            LtcsProvisionReportFormDelegate::class => LtcsProvisionReportFormDelegateImpl::class,
            LtcsProvisionReportRepository::class => LtcsProvisionReportRepositoryEloquentImpl::class,
            RunCreateDwsServiceReportPreviewJobUseCase::class => RunCreateDwsServiceReportPreviewJobInteractor::class,
            RunCreateLtcsProvisionReportSheetJobUseCase::class => RunCreateLtcsProvisionReportSheetJobInteractor::class,
            UpdateDwsProvisionReportStatusUseCase::class => UpdateDwsProvisionReportStatusInteractor::class,
            UpdateDwsProvisionReportUseCase::class => UpdateDwsProvisionReportInteractor::class,
            UpdateLtcsProvisionReportStatusUseCase::class => UpdateLtcsProvisionReportStatusInteractor::class,
            UpdateLtcsProvisionReportUseCase::class => UpdateLtcsProvisionReportInteractor::class,
        ];
    }
}

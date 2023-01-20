<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Billing\DwsBillingBundleFinder;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingFinder;
use Domain\Billing\DwsBillingInvoiceFinder;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingServiceReportFinder;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsHomeHelpServiceChunkFinder;
use Domain\Billing\DwsHomeHelpServiceChunkRepository;
use Domain\Billing\DwsVisitingCareForPwsdChunkFinder;
use Domain\Billing\DwsVisitingCareForPwsdChunkRepository;
use Domain\Billing\LtcsBillingBundleFinder;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingFinder;
use Domain\Billing\LtcsBillingInvoiceFinder;
use Domain\Billing\LtcsBillingInvoiceRepository;
use Domain\Billing\LtcsBillingRepository;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Billing\LtcsBillingStatementRepository;
use Infrastructure\Billing\DwsBillingBundleFinderEloquentImpl;
use Infrastructure\Billing\DwsBillingBundleRepositoryEloquentImpl;
use Infrastructure\Billing\DwsBillingCopayCoordinationFinderEloquentImpl;
use Infrastructure\Billing\DwsBillingCopayCoordinationRepositoryEloquentImpl;
use Infrastructure\Billing\DwsBillingFinderEloquentImpl;
use Infrastructure\Billing\DwsBillingInvoiceFinderEloquentImpl;
use Infrastructure\Billing\DwsBillingInvoiceRepositoryEloquentImpl;
use Infrastructure\Billing\DwsBillingRepositoryEloquentImpl;
use Infrastructure\Billing\DwsBillingServiceReportFinderEloquentImpl;
use Infrastructure\Billing\DwsBillingServiceReportRepositoryEloquentImpl;
use Infrastructure\Billing\DwsBillingStatementFinderEloquentImpl;
use Infrastructure\Billing\DwsBillingStatementRepositoryEloquentImpl;
use Infrastructure\Billing\DwsHomeHelpServiceChunkFinderEloquentImpl;
use Infrastructure\Billing\DwsHomeHelpServiceChunkRepositoryEloquentImpl;
use Infrastructure\Billing\DwsVisitingCareForPwsdChunkFinderEloquentImpl;
use Infrastructure\Billing\DwsVisitingCareForPwsdChunkRepositoryEloquentImpl;
use Infrastructure\Billing\LtcsBillingBundleFinderEloquentImpl;
use Infrastructure\Billing\LtcsBillingBundleRepositoryEloquentImpl;
use Infrastructure\Billing\LtcsBillingFinderEloquentImpl;
use Infrastructure\Billing\LtcsBillingInvoiceFinderEloquentImpl;
use Infrastructure\Billing\LtcsBillingInvoiceRepositoryEloquentImpl;
use Infrastructure\Billing\LtcsBillingRepositoryEloquentImpl;
use Infrastructure\Billing\LtcsBillingStatementFinderEloquentImpl;
use Infrastructure\Billing\LtcsBillingStatementRepositoryEloquentImpl;
use UseCase\Billing\BuildCopayListPdfParamInteractor;
use UseCase\Billing\BuildCopayListPdfParamUseCase;
use UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamInteractor;
use UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamUseCase;
use UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListInteractor;
use UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListUseCase;
use UseCase\Billing\BuildDwsBillingInvoiceInteractor;
use UseCase\Billing\BuildDwsBillingInvoicePdfParamInteractor;
use UseCase\Billing\BuildDwsBillingInvoicePdfParamUseCase;
use UseCase\Billing\BuildDwsBillingInvoiceUseCase;
use UseCase\Billing\BuildDwsBillingServiceDetailListInteractor;
use UseCase\Billing\BuildDwsBillingServiceDetailListUseCase;
use UseCase\Billing\BuildDwsBillingServiceReportListByIdInteractor;
use UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase;
use UseCase\Billing\BuildDwsBillingServiceReportListInteractor;
use UseCase\Billing\BuildDwsBillingServiceReportListUseCase;
use UseCase\Billing\BuildDwsBillingServiceReportRecordListInteractor;
use UseCase\Billing\BuildDwsBillingServiceReportRecordListUseCase;
use UseCase\Billing\BuildDwsBillingSourceListInteractor;
use UseCase\Billing\BuildDwsBillingSourceListUseCase;
use UseCase\Billing\BuildDwsBillingStatementAggregateListInteractor;
use UseCase\Billing\BuildDwsBillingStatementAggregateListUseCase;
use UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListInteractor;
use UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListUseCase;
use UseCase\Billing\BuildDwsBillingStatementContractListInteractor;
use UseCase\Billing\BuildDwsBillingStatementContractListUseCase;
use UseCase\Billing\BuildDwsBillingStatementElementListInteractor;
use UseCase\Billing\BuildDwsBillingStatementElementListUseCase;
use UseCase\Billing\BuildDwsBillingStatementForUpdateInteractor;
use UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase;
use UseCase\Billing\BuildDwsBillingStatementInteractor;
use UseCase\Billing\BuildDwsBillingStatementUseCase;
use UseCase\Billing\BuildDwsHomeHelpServiceServiceDetailListInteractor;
use UseCase\Billing\BuildDwsHomeHelpServiceServiceDetailListUseCase;
use UseCase\Billing\BuildDwsHomeHelpServiceServiceReportPdfParamInteractor;
use UseCase\Billing\BuildDwsHomeHelpServiceServiceReportPdfParamUseCase;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListInteractor;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListUseCase;
use UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListInteractor;
use UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListUseCase;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListInteractor;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListUseCase;
use UseCase\Billing\BuildLtcsBillingInvoiceListInteractor;
use UseCase\Billing\BuildLtcsBillingInvoiceListUseCase;
use UseCase\Billing\BuildLtcsBillingInvoicePdfParamInteractor;
use UseCase\Billing\BuildLtcsBillingInvoicePdfParamUseCase;
use UseCase\Billing\BuildLtcsBillingInvoiceRecordListInteractor;
use UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase;
use UseCase\Billing\BuildLtcsBillingStatementInteractor;
use UseCase\Billing\BuildLtcsBillingStatementUseCase;
use UseCase\Billing\BuildLtcsServiceDetailListInteractor;
use UseCase\Billing\BuildLtcsServiceDetailListUseCase;
use UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusInteractor;
use UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusUseCase;
use UseCase\Billing\BulkUpdateDwsBillingStatementStatusInteractor;
use UseCase\Billing\BulkUpdateDwsBillingStatementStatusUseCase;
use UseCase\Billing\BulkUpdateLtcsBillingStatementStatusInteractor;
use UseCase\Billing\BulkUpdateLtcsBillingStatementStatusUseCase;
use UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionInteractor;
use UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase;
use UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractor;
use UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingEmergencyAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingLocationAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingLocationAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingUserLocationAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingUserLocationAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase;
use UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor;
use UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase;
use UseCase\Billing\ConfirmDwsBillingStatusInteractor;
use UseCase\Billing\ConfirmDwsBillingStatusUseCase;
use UseCase\Billing\ConfirmLtcsBillingStatementStatusInteractor;
use UseCase\Billing\ConfirmLtcsBillingStatementStatusUseCase;
use UseCase\Billing\CopyDwsBillingInteractor;
use UseCase\Billing\CopyDwsBillingUseCase;
use UseCase\Billing\CreateDwsBillingBundleListInteractor;
use UseCase\Billing\CreateDwsBillingBundleListUseCase;
use UseCase\Billing\CreateDwsBillingCopayCoordinationCsvInteractor;
use UseCase\Billing\CreateDwsBillingCopayCoordinationCsvUseCase;
use UseCase\Billing\CreateDwsBillingCopayCoordinationInteractor;
use UseCase\Billing\CreateDwsBillingCopayCoordinationPdfInteractor;
use UseCase\Billing\CreateDwsBillingCopayCoordinationPdfUseCase;
use UseCase\Billing\CreateDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\CreateDwsBillingInteractor;
use UseCase\Billing\CreateDwsBillingInvoiceInteractor;
use UseCase\Billing\CreateDwsBillingInvoicePdfInteractor;
use UseCase\Billing\CreateDwsBillingInvoicePdfUseCase;
use UseCase\Billing\CreateDwsBillingInvoiceUseCase;
use UseCase\Billing\CreateDwsBillingServiceReportCsvInteractor;
use UseCase\Billing\CreateDwsBillingServiceReportCsvUseCase;
use UseCase\Billing\CreateDwsBillingServiceReportListInteractor;
use UseCase\Billing\CreateDwsBillingServiceReportListUseCase;
use UseCase\Billing\CreateDwsBillingServiceReportPdfInteractor;
use UseCase\Billing\CreateDwsBillingServiceReportPdfUseCase;
use UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvInteractor;
use UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvUseCase;
use UseCase\Billing\CreateDwsBillingStatementListInteractor;
use UseCase\Billing\CreateDwsBillingStatementListUseCase;
use UseCase\Billing\CreateDwsBillingUseCase;
use UseCase\Billing\CreateDwsHomeHelpServiceChunkListInteractor;
use UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase;
use UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListInteractor;
use UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase;
use UseCase\Billing\CreateLtcsBillingBundleInteractor;
use UseCase\Billing\CreateLtcsBillingBundleUseCase;
use UseCase\Billing\CreateLtcsBillingInteractor;
use UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor;
use UseCase\Billing\CreateLtcsBillingInvoiceCsvUseCase;
use UseCase\Billing\CreateLtcsBillingInvoiceListInteractor;
use UseCase\Billing\CreateLtcsBillingInvoiceListUseCase;
use UseCase\Billing\CreateLtcsBillingInvoicePdfInteractor;
use UseCase\Billing\CreateLtcsBillingInvoicePdfUseCase;
use UseCase\Billing\CreateLtcsBillingStatementInteractor;
use UseCase\Billing\CreateLtcsBillingStatementListInteractor;
use UseCase\Billing\CreateLtcsBillingStatementListUseCase;
use UseCase\Billing\CreateLtcsBillingStatementUseCase;
use UseCase\Billing\CreateLtcsBillingUseCase;
use UseCase\Billing\DownloadDwsBillingCopayCoordinationInteractor;
use UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\EditDwsBillingCopayCoordinationInteractor;
use UseCase\Billing\EditDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\EditDwsBillingInteractor;
use UseCase\Billing\EditDwsBillingServiceReportInteractor;
use UseCase\Billing\EditDwsBillingServiceReportUseCase;
use UseCase\Billing\EditDwsBillingStatementInteractor;
use UseCase\Billing\EditDwsBillingStatementStatusInteractor;
use UseCase\Billing\EditDwsBillingStatementStatusUseCase;
use UseCase\Billing\EditDwsBillingStatementUseCase;
use UseCase\Billing\EditDwsBillingUseCase;
use UseCase\Billing\EditLtcsBillingInteractor;
use UseCase\Billing\EditLtcsBillingUseCase;
use UseCase\Billing\EnsureDwsBillingBundleInteractor;
use UseCase\Billing\EnsureDwsBillingBundleUseCase;
use UseCase\Billing\EnsureDwsBillingInteractor;
use UseCase\Billing\EnsureDwsBillingUseCase;
use UseCase\Billing\EnsureLtcsBillingBundleInteractor;
use UseCase\Billing\EnsureLtcsBillingBundleUseCase;
use UseCase\Billing\EnsureLtcsBillingInteractor;
use UseCase\Billing\EnsureLtcsBillingUseCase;
use UseCase\Billing\FindDwsBillingInteractor;
use UseCase\Billing\FindDwsBillingUseCase;
use UseCase\Billing\FindLtcsBillingInteractor;
use UseCase\Billing\FindLtcsBillingUseCase;
use UseCase\Billing\GenerateCopayListPdfInteractor;
use UseCase\Billing\GenerateCopayListPdfUseCase;
use UseCase\Billing\GetDwsBillingCopayCoordinationInfoInteractor;
use UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase;
use UseCase\Billing\GetDwsBillingFileInfoInteractor;
use UseCase\Billing\GetDwsBillingFileInfoUseCase;
use UseCase\Billing\GetDwsBillingInfoInteractor;
use UseCase\Billing\GetDwsBillingInfoUseCase;
use UseCase\Billing\GetDwsBillingServiceReportInfoInteractor;
use UseCase\Billing\GetDwsBillingServiceReportInfoUseCase;
use UseCase\Billing\GetDwsBillingStatementInfoInteractor;
use UseCase\Billing\GetDwsBillingStatementInfoUseCase;
use UseCase\Billing\GetLtcsBillingFileInfoInteractor;
use UseCase\Billing\GetLtcsBillingFileInfoUseCase;
use UseCase\Billing\GetLtcsBillingInfoInteractor;
use UseCase\Billing\GetLtcsBillingInfoUseCase;
use UseCase\Billing\GetLtcsBillingStatementInfoInteractor;
use UseCase\Billing\GetLtcsBillingStatementInfoUseCase;
use UseCase\Billing\LookupDwsBillingBundleInteractor;
use UseCase\Billing\LookupDwsBillingBundleUseCase;
use UseCase\Billing\LookupDwsBillingCopayCoordinationInteractor;
use UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\LookupDwsBillingInteractor;
use UseCase\Billing\LookupDwsBillingServiceReportInteractor;
use UseCase\Billing\LookupDwsBillingServiceReportUseCase;
use UseCase\Billing\LookupDwsBillingStatementInteractor;
use UseCase\Billing\LookupDwsBillingStatementUseCase;
use UseCase\Billing\LookupDwsBillingUseCase;
use UseCase\Billing\LookupLtcsBillingBundleInteractor;
use UseCase\Billing\LookupLtcsBillingBundleUseCase;
use UseCase\Billing\LookupLtcsBillingInteractor;
use UseCase\Billing\LookupLtcsBillingStatementInteractor;
use UseCase\Billing\LookupLtcsBillingStatementUseCase;
use UseCase\Billing\LookupLtcsBillingUseCase;
use UseCase\Billing\RefreshDwsBillingCopayCoordinationInteractor;
use UseCase\Billing\RefreshDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\RefreshDwsBillingServiceReportInteractor;
use UseCase\Billing\RefreshDwsBillingServiceReportUseCase;
use UseCase\Billing\RefreshDwsBillingStatementInteractor;
use UseCase\Billing\RefreshDwsBillingStatementUseCase;
use UseCase\Billing\RefreshLtcsBillingStatementInteractor;
use UseCase\Billing\RefreshLtcsBillingStatementUseCase;
use UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobInteractor;
use UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase;
use UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobInteractor;
use UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobUseCase;
use UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobInteractor;
use UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase;
use UseCase\Billing\RunCopyDwsBillingJobInteractor;
use UseCase\Billing\RunCopyDwsBillingJobUseCase;
use UseCase\Billing\RunCreateCopayListJobInteractor;
use UseCase\Billing\RunCreateCopayListJobUseCase;
use UseCase\Billing\RunCreateDwsBillingJobInteractor;
use UseCase\Billing\RunCreateDwsBillingJobUseCase;
use UseCase\Billing\RunCreateLtcsBillingJobInteractor;
use UseCase\Billing\RunCreateLtcsBillingJobUseCase;
use UseCase\Billing\RunRefreshDwsBillingStatementJobInteractor;
use UseCase\Billing\RunRefreshDwsBillingStatementJobUseCase;
use UseCase\Billing\RunRefreshLtcsBillingStatementJobInteractor;
use UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase;
use UseCase\Billing\RunUpdateDwsBillingFilesJobInteractor;
use UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase;
use UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor;
use UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase;
use UseCase\Billing\SimpleLookupDwsBillingServiceReportInteractor;
use UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase;
use UseCase\Billing\SimpleLookupDwsBillingStatementInteractor;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;
use UseCase\Billing\SimpleLookupLtcsBillingStatementInteractor;
use UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase;
use UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusInteractor;
use UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase;
use UseCase\Billing\UpdateDwsBillingFilesInteractor;
use UseCase\Billing\UpdateDwsBillingFilesUseCase;
use UseCase\Billing\UpdateDwsBillingInvoiceInteractor;
use UseCase\Billing\UpdateDwsBillingInvoiceUseCase;
use UseCase\Billing\UpdateDwsBillingServiceReportStatusInteractor;
use UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationInteractor;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationStatusInteractor;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationStatusUseCase;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase;
use UseCase\Billing\UpdateDwsBillingStatementInteractor;
use UseCase\Billing\UpdateDwsBillingStatementUseCase;
use UseCase\Billing\UpdateDwsBillingStatusInteractor;
use UseCase\Billing\UpdateDwsBillingStatusUseCase;
use UseCase\Billing\UpdateLtcsBillingFilesInteractor;
use UseCase\Billing\UpdateLtcsBillingFilesUseCase;
use UseCase\Billing\UpdateLtcsBillingInvoiceListInteractor;
use UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase;
use UseCase\Billing\UpdateLtcsBillingStatementInteractor;
use UseCase\Billing\UpdateLtcsBillingStatementStatusInteractor;
use UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase;
use UseCase\Billing\UpdateLtcsBillingStatementUseCase;
use UseCase\Billing\UpdateLtcsBillingStatusInteractor;
use UseCase\Billing\UpdateLtcsBillingStatusUseCase;
use UseCase\Billing\ValidateCopayCoordinationItemInteractor;
use UseCase\Billing\ValidateCopayCoordinationItemUseCase;

/**
 * Billing Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
class BillingDependencies implements DependenciesInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependenciesList(): iterable
    {
        // Infrastructure
        yield from [
            ComputeLtcsBillingEmergencyAdditionUseCase::class => ComputeLtcsBillingEmergencyAdditionInteractor::class,
            ComputeLtcsBillingFirstTimeAdditionUseCase::class => ComputeLtcsBillingFirstTimeAdditionInteractor::class,
            ComputeLtcsBillingLocationAdditionUseCase::class => ComputeLtcsBillingLocationAdditionInteractor::class,
            ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase::class => ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor::class,
            ComputeLtcsBillingTreatmentImprovementAdditionUseCase::class => ComputeLtcsBillingTreatmentImprovementAdditionInteractor::class,
            ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase::class => ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor::class,
            DwsBillingBundleFinder::class => DwsBillingBundleFinderEloquentImpl::class,
            DwsBillingBundleRepository::class => DwsBillingBundleRepositoryEloquentImpl::class,
            DwsBillingCopayCoordinationFinder::class => DwsBillingCopayCoordinationFinderEloquentImpl::class,
            DwsBillingCopayCoordinationRepository::class => DwsBillingCopayCoordinationRepositoryEloquentImpl::class,
            DwsBillingFinder::class => DwsBillingFinderEloquentImpl::class,
            DwsBillingInvoiceFinder::class => DwsBillingInvoiceFinderEloquentImpl::class,
            DwsBillingInvoiceRepository::class => DwsBillingInvoiceRepositoryEloquentImpl::class,
            DwsBillingRepository::class => DwsBillingRepositoryEloquentImpl::class,
            DwsBillingServiceReportFinder::class => DwsBillingServiceReportFinderEloquentImpl::class,
            DwsBillingServiceReportRepository::class => DwsBillingServiceReportRepositoryEloquentImpl::class,
            DwsBillingStatementFinder::class => DwsBillingStatementFinderEloquentImpl::class,
            DwsBillingStatementRepository::class => DwsBillingStatementRepositoryEloquentImpl::class,
            DwsHomeHelpServiceChunkFinder::class => DwsHomeHelpServiceChunkFinderEloquentImpl::class,
            DwsHomeHelpServiceChunkRepository::class => DwsHomeHelpServiceChunkRepositoryEloquentImpl::class,
            DwsVisitingCareForPwsdChunkFinder::class => DwsVisitingCareForPwsdChunkFinderEloquentImpl::class,
            DwsVisitingCareForPwsdChunkRepository::class => DwsVisitingCareForPwsdChunkRepositoryEloquentImpl::class,
            LtcsBillingBundleFinder::class => LtcsBillingBundleFinderEloquentImpl::class,
            LtcsBillingBundleRepository::class => LtcsBillingBundleRepositoryEloquentImpl::class,
            LtcsBillingFinder::class => LtcsBillingFinderEloquentImpl::class,
            LtcsBillingInvoiceFinder::class => LtcsBillingInvoiceFinderEloquentImpl::class,
            LtcsBillingInvoiceRepository::class => LtcsBillingInvoiceRepositoryEloquentImpl::class,
            LtcsBillingRepository::class => LtcsBillingRepositoryEloquentImpl::class,
            LtcsBillingStatementFinder::class => LtcsBillingStatementFinderEloquentImpl::class,
            LtcsBillingStatementRepository::class => LtcsBillingStatementRepositoryEloquentImpl::class,
        ];
        // UseCase
        yield from [
            BuildCopayListPdfParamUseCase::class => BuildCopayListPdfParamInteractor::class,
            BuildDwsBillingCopayCoordinationPdfParamUseCase::class => BuildDwsBillingCopayCoordinationPdfParamInteractor::class,
            BuildDwsBillingCopayCoordinationRecordListUseCase::class => BuildDwsBillingCopayCoordinationRecordListInteractor::class,
            BuildDwsBillingInvoicePdfParamUseCase::class => BuildDwsBillingInvoicePdfParamInteractor::class,
            BuildDwsBillingInvoiceUseCase::class => BuildDwsBillingInvoiceInteractor::class,
            BuildDwsBillingServiceDetailListUseCase::class => BuildDwsBillingServiceDetailListInteractor::class,
            BuildDwsBillingServiceReportListByIdUseCase::class => BuildDwsBillingServiceReportListByIdInteractor::class,
            BuildDwsBillingServiceReportListUseCase::class => BuildDwsBillingServiceReportListInteractor::class,
            BuildDwsBillingServiceReportRecordListUseCase::class => BuildDwsBillingServiceReportRecordListInteractor::class,
            BuildDwsBillingSourceListUseCase::class => BuildDwsBillingSourceListInteractor::class,
            BuildDwsBillingStatementAggregateListUseCase::class => BuildDwsBillingStatementAggregateListInteractor::class,
            BuildDwsBillingStatementAndInvoiceRecordListUseCase::class => BuildDwsBillingStatementAndInvoiceRecordListInteractor::class,
            BuildDwsBillingStatementContractListUseCase::class => BuildDwsBillingStatementContractListInteractor::class,
            BuildDwsBillingStatementElementListUseCase::class => BuildDwsBillingStatementElementListInteractor::class,
            BuildDwsBillingStatementForUpdateUseCase::class => BuildDwsBillingStatementForUpdateInteractor::class,
            BuildDwsBillingStatementUseCase::class => BuildDwsBillingStatementInteractor::class,
            BuildDwsHomeHelpServiceServiceDetailListUseCase::class => BuildDwsHomeHelpServiceServiceDetailListInteractor::class,
            BuildDwsHomeHelpServiceServiceReportPdfParamUseCase::class => BuildDwsHomeHelpServiceServiceReportPdfParamInteractor::class,
            BuildDwsHomeHelpServiceUnitListUseCase::class => BuildDwsHomeHelpServiceUnitListInteractor::class,
            BuildDwsVisitingCareForPwsdServiceDetailListUseCase::class => BuildDwsVisitingCareForPwsdServiceDetailListInteractor::class,
            BuildDwsVisitingCareForPwsdUnitListUseCase::class => BuildDwsVisitingCareForPwsdUnitListInteractor::class,
            BuildLtcsBillingInvoiceListUseCase::class => BuildLtcsBillingInvoiceListInteractor::class,
            BuildLtcsBillingInvoicePdfParamUseCase::class => BuildLtcsBillingInvoicePdfParamInteractor::class,
            BuildLtcsBillingInvoiceRecordListUseCase::class => BuildLtcsBillingInvoiceRecordListInteractor::class,
            BuildLtcsBillingStatementUseCase::class => BuildLtcsBillingStatementInteractor::class,
            BuildLtcsServiceDetailListUseCase::class => BuildLtcsServiceDetailListInteractor::class,
            BulkUpdateDwsBillingServiceReportStatusUseCase::class => BulkUpdateDwsBillingServiceReportStatusInteractor::class,
            BulkUpdateDwsBillingStatementStatusUseCase::class => BulkUpdateDwsBillingStatementStatusInteractor::class,
            BulkUpdateLtcsBillingStatementStatusUseCase::class => BulkUpdateLtcsBillingStatementStatusInteractor::class,
            ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase::class => ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionInteractor::class,
            ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase::class => ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractor::class,
            ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase::class => ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor::class,
            ComputeLtcsBillingUserLocationAdditionUseCase::class => ComputeLtcsBillingUserLocationAdditionInteractor::class,
            ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase::class => ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor::class,
            ConfirmDwsBillingStatusUseCase::class => ConfirmDwsBillingStatusInteractor::class,
            ConfirmLtcsBillingStatementStatusUseCase::class => ConfirmLtcsBillingStatementStatusInteractor::class,
            CopyDwsBillingUseCase::class => CopyDwsBillingInteractor::class,
            CreateDwsBillingBundleListUseCase::class => CreateDwsBillingBundleListInteractor::class,
            CreateDwsBillingCopayCoordinationCsvUseCase::class => CreateDwsBillingCopayCoordinationCsvInteractor::class,
            CreateDwsBillingCopayCoordinationPdfUseCase::class => CreateDwsBillingCopayCoordinationPdfInteractor::class,
            CreateDwsBillingCopayCoordinationUseCase::class => CreateDwsBillingCopayCoordinationInteractor::class,
            CreateDwsBillingInvoicePdfUseCase::class => CreateDwsBillingInvoicePdfInteractor::class,
            CreateDwsBillingInvoiceUseCase::class => CreateDwsBillingInvoiceInteractor::class,
            CreateDwsBillingServiceReportCsvUseCase::class => CreateDwsBillingServiceReportCsvInteractor::class,
            CreateDwsBillingServiceReportListUseCase::class => CreateDwsBillingServiceReportListInteractor::class,
            CreateDwsBillingServiceReportPdfUseCase::class => CreateDwsBillingServiceReportPdfInteractor::class,
            CreateDwsBillingStatementAndInvoiceCsvUseCase::class => CreateDwsBillingStatementAndInvoiceCsvInteractor::class,
            CreateDwsBillingStatementListUseCase::class => CreateDwsBillingStatementListInteractor::class,
            CreateDwsBillingUseCase::class => CreateDwsBillingInteractor::class,
            CreateDwsHomeHelpServiceChunkListUseCase::class => CreateDwsHomeHelpServiceChunkListInteractor::class,
            CreateDwsVisitingCareForPwsdChunkListUseCase::class => CreateDwsVisitingCareForPwsdChunkListInteractor::class,
            CreateLtcsBillingBundleUseCase::class => CreateLtcsBillingBundleInteractor::class,
            CreateLtcsBillingInvoiceCsvUseCase::class => CreateLtcsBillingInvoiceCsvInteractor::class,
            CreateLtcsBillingInvoiceListUseCase::class => CreateLtcsBillingInvoiceListInteractor::class,
            CreateLtcsBillingInvoicePdfUseCase::class => CreateLtcsBillingInvoicePdfInteractor::class,
            CreateLtcsBillingStatementListUseCase::class => CreateLtcsBillingStatementListInteractor::class,
            CreateLtcsBillingStatementUseCase::class => CreateLtcsBillingStatementInteractor::class,
            CreateLtcsBillingUseCase::class => CreateLtcsBillingInteractor::class,
            DownloadDwsBillingCopayCoordinationUseCase::class => DownloadDwsBillingCopayCoordinationInteractor::class,
            // 未実装
            // EditDwsBillingCopayCoordinationUseCase::class => EditDwsBillingCopayCoordinationInteractor::class,
            EditDwsBillingServiceReportUseCase::class => EditDwsBillingServiceReportInteractor::class,
            // 未実装
            // EditDwsBillingStatementCopayCoordinationStatusUseCase::class => EditDwsBillingStatementCopayCoordinationStatusInteractor::class,
            // 未実装
            // EditDwsBillingStatementCopayCoordinationUseCase::class => EditDwsBillingStatementCopayCoordinationInteractor::class,
            EditDwsBillingStatementStatusUseCase::class => EditDwsBillingStatementStatusInteractor::class,
            EditDwsBillingStatementUseCase::class => EditDwsBillingStatementInteractor::class,
            // 未実装
            // EditDwsBillingStatusUseCase::class => EditDwsBillingStatusInteractor::class,
            EditDwsBillingUseCase::class => EditDwsBillingInteractor::class,
            EditLtcsBillingUseCase::class => EditLtcsBillingInteractor::class,
            EnsureDwsBillingBundleUseCase::class => EnsureDwsBillingBundleInteractor::class,
            EnsureDwsBillingUseCase::class => EnsureDwsBillingInteractor::class,
            EnsureLtcsBillingBundleUseCase::class => EnsureLtcsBillingBundleInteractor::class,
            EnsureLtcsBillingUseCase::class => EnsureLtcsBillingInteractor::class,
            FindDwsBillingUseCase::class => FindDwsBillingInteractor::class,
            FindLtcsBillingUseCase::class => FindLtcsBillingInteractor::class,
            GenerateCopayListPdfUseCase::class => GenerateCopayListPdfInteractor::class,
            GetDwsBillingCopayCoordinationInfoUseCase::class => GetDwsBillingCopayCoordinationInfoInteractor::class,
            GetDwsBillingFileInfoUseCase::class => GetDwsBillingFileInfoInteractor::class,
            GetDwsBillingInfoUseCase::class => GetDwsBillingInfoInteractor::class,
            GetDwsBillingServiceReportInfoUseCase::class => GetDwsBillingServiceReportInfoInteractor::class,
            GetDwsBillingStatementInfoUseCase::class => GetDwsBillingStatementInfoInteractor::class,
            GetLtcsBillingFileInfoUseCase::class => GetLtcsBillingFileInfoInteractor::class,
            GetLtcsBillingInfoUseCase::class => GetLtcsBillingInfoInteractor::class,
            GetLtcsBillingStatementInfoUseCase::class => GetLtcsBillingStatementInfoInteractor::class,
            // 未実装
            // IdentifyDwsBillingStatementUseCase::class => IdentifyDwsBillingStatementInteractor::class,
            LookupDwsBillingBundleUseCase::class => LookupDwsBillingBundleInteractor::class,
            LookupDwsBillingCopayCoordinationUseCase::class => LookupDwsBillingCopayCoordinationInteractor::class,
            LookupDwsBillingServiceReportUseCase::class => LookupDwsBillingServiceReportInteractor::class,
            LookupDwsBillingStatementUseCase::class => LookupDwsBillingStatementInteractor::class,
            LookupDwsBillingUseCase::class => LookupDwsBillingInteractor::class,
            LookupLtcsBillingBundleUseCase::class => LookupLtcsBillingBundleInteractor::class,
            LookupLtcsBillingStatementUseCase::class => LookupLtcsBillingStatementInteractor::class,
            LookupLtcsBillingUseCase::class => LookupLtcsBillingInteractor::class,
            RefreshDwsBillingCopayCoordinationUseCase::class => RefreshDwsBillingCopayCoordinationInteractor::class,
            RefreshDwsBillingServiceReportUseCase::class => RefreshDwsBillingServiceReportInteractor::class,
            RefreshDwsBillingStatementUseCase::class => RefreshDwsBillingStatementInteractor::class,
            RefreshLtcsBillingStatementUseCase::class => RefreshLtcsBillingStatementInteractor::class,
            RunBulkUpdateDwsBillingStatementStatusJobUseCase::class => RunBulkUpdateDwsBillingStatementStatusJobInteractor::class,
            RunBulkUpdateLtcsBillingStatementStatusJobUseCase::class => RunBulkUpdateLtcsBillingStatementStatusJobInteractor::class,
            RunBulkUpdateDwsBillingServiceReportStatusJobUseCase::class => RunBulkUpdateDwsBillingServiceReportStatusJobInteractor::class,
            RunCopyDwsBillingJobUseCase::class => RunCopyDwsBillingJobInteractor::class,
            RunCreateCopayListJobUseCase::class => RunCreateCopayListJobInteractor::class,
            RunCreateDwsBillingJobUseCase::class => RunCreateDwsBillingJobInteractor::class,
            RunCreateLtcsBillingJobUseCase::class => RunCreateLtcsBillingJobInteractor::class,
            RunRefreshDwsBillingStatementJobUseCase::class => RunRefreshDwsBillingStatementJobInteractor::class,
            RunRefreshLtcsBillingStatementJobUseCase::class => RunRefreshLtcsBillingStatementJobInteractor::class,
            RunUpdateDwsBillingFilesJobUseCase::class => RunUpdateDwsBillingFilesJobInteractor::class,
            RunUpdateLtcsBillingFilesJobUseCase::class => RunUpdateLtcsBillingFilesJobInteractor::class,
            SimpleLookupDwsBillingServiceReportUseCase::class => SimpleLookupDwsBillingServiceReportInteractor::class,
            SimpleLookupDwsBillingStatementUseCase::class => SimpleLookupDwsBillingStatementInteractor::class,
            SimpleLookupLtcsBillingStatementUseCase::class => SimpleLookupLtcsBillingStatementInteractor::class,
            UpdateDwsBillingCopayCoordinationStatusUseCase::class => UpdateDwsBillingCopayCoordinationStatusInteractor::class,
            EditDwsBillingCopayCoordinationUseCase::class => EditDwsBillingCopayCoordinationInteractor::class,
            UpdateDwsBillingFilesUseCase::class => UpdateDwsBillingFilesInteractor::class,
            UpdateDwsBillingInvoiceUseCase::class => UpdateDwsBillingInvoiceInteractor::class,
            UpdateDwsBillingServiceReportStatusUseCase::class => UpdateDwsBillingServiceReportStatusInteractor::class,
            UpdateDwsBillingStatementCopayCoordinationStatusUseCase::class => UpdateDwsBillingStatementCopayCoordinationStatusInteractor::class,
            UpdateDwsBillingStatementCopayCoordinationUseCase::class => UpdateDwsBillingStatementCopayCoordinationInteractor::class,
            UpdateDwsBillingStatementUseCase::class => UpdateDwsBillingStatementInteractor::class,
            UpdateDwsBillingStatusUseCase::class => UpdateDwsBillingStatusInteractor::class,
            UpdateLtcsBillingFilesUseCase::class => UpdateLtcsBillingFilesInteractor::class,
            UpdateLtcsBillingInvoiceListUseCase::class => UpdateLtcsBillingInvoiceListInteractor::class,
            UpdateLtcsBillingStatementStatusUseCase::class => UpdateLtcsBillingStatementStatusInteractor::class,
            UpdateLtcsBillingStatementUseCase::class => UpdateLtcsBillingStatementInteractor::class,
            UpdateLtcsBillingStatusUseCase::class => UpdateLtcsBillingStatusInteractor::class,
            ValidateCopayCoordinationItemUseCase::class => ValidateCopayCoordinationItemInteractor::class,
        ];
    }
}

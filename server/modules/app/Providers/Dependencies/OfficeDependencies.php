<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Office\HomeHelpServiceCalcSpecFinder;
use Domain\Office\HomeHelpServiceCalcSpecRepository;
use Domain\Office\HomeVisitLongTermCareCalcSpecFinder;
use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use Domain\Office\OfficeFinder;
use Domain\Office\OfficeGroupFinder;
use Domain\Office\OfficeGroupRepository;
use Domain\Office\OfficeRepository;
use Domain\Office\VisitingCareForPwsdCalcSpecFinder;
use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use Infrastructure\Office\HomeHelpServiceCalcSpecFinderEloquentImpl;
use Infrastructure\Office\HomeHelpServiceCalcSpecRepositoryEloquentImpl;
use Infrastructure\Office\HomeVisitLongTermCareCalcSpecFinderEloquentImpl;
use Infrastructure\Office\HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl;
use Infrastructure\Office\OfficeFinderEloquentImpl;
use Infrastructure\Office\OfficeGroupFinderEloquentImpl;
use Infrastructure\Office\OfficeGroupRepositoryEloquentImpl;
use Infrastructure\Office\OfficeRepositoryEloquentImpl;
use Infrastructure\Office\VisitingCareForPwsdCalcSpecFinderEloquentImpl;
use Infrastructure\Office\VisitingCareForPwsdCalcSpecRepositoryEloquentImpl;
use UseCase\Office\BulkEditOfficeGroupInteractor;
use UseCase\Office\BulkEditOfficeGroupUseCase;
use UseCase\Office\CreateHomeHelpServiceCalcSpecInteractor;
use UseCase\Office\CreateHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\CreateHomeVisitLongTermCareCalcSpecInteractor;
use UseCase\Office\CreateHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\CreateOfficeGroupInteractor;
use UseCase\Office\CreateOfficeGroupUseCase;
use UseCase\Office\CreateOfficeInteractor;
use UseCase\Office\CreateOfficeUseCase;
use UseCase\Office\CreateVisitingCareForPwsdCalcSpecInteractor;
use UseCase\Office\CreateVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\DeleteOfficeGroupInteractor;
use UseCase\Office\DeleteOfficeGroupUseCase;
use UseCase\Office\EditHomeHelpServiceCalcSpecInteractor;
use UseCase\Office\EditHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\EditHomeVisitLongTermCareCalcSpecInteractor;
use UseCase\Office\EditHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\EditOfficeGroupInteractor;
use UseCase\Office\EditOfficeGroupUseCase;
use UseCase\Office\EditOfficeInteractor;
use UseCase\Office\EditOfficeUseCase;
use UseCase\Office\EditVisitingCareForPwsdCalcSpecInteractor;
use UseCase\Office\EditVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\EnsureOfficeInteractor;
use UseCase\Office\EnsureOfficeUseCase;
use UseCase\Office\FindHomeHelpServiceCalcSpecInteractor;
use UseCase\Office\FindHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\FindHomeVisitLongTermCareCalcSpecInteractor;
use UseCase\Office\FindHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\FindOfficeGroupInteractor;
use UseCase\Office\FindOfficeGroupUseCase;
use UseCase\Office\FindOfficeInteractor;
use UseCase\Office\FindOfficeUseCase;
use UseCase\Office\FindVisitingCareForPwsdCalcSpecInteractor;
use UseCase\Office\FindVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\GetHomeVisitLongTermCareCalcSpecInteractor;
use UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\GetIndexOfficeGroupOptionInteractor;
use UseCase\Office\GetIndexOfficeGroupOptionUseCase;
use UseCase\Office\GetIndexOfficeInteractor;
use UseCase\Office\GetIndexOfficeOptionInteractor;
use UseCase\Office\GetIndexOfficeOptionUseCase;
use UseCase\Office\GetIndexOfficeUseCase;
use UseCase\Office\GetOfficeInfoInteractor;
use UseCase\Office\GetOfficeInfoUseCase;
use UseCase\Office\GetOfficeListInteractor;
use UseCase\Office\GetOfficeListUseCase;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecInteractor;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\IdentifyHomeVisitLongTermCareCalcSpecInteractor;
use UseCase\Office\IdentifyHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecInteractor;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\ImportOfficeInteractor;
use UseCase\Office\ImportOfficeUseCase;
use UseCase\Office\LookupHomeHelpServiceCalcSpecInteractor;
use UseCase\Office\LookupHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\LookupHomeVisitLongTermCareCalcSpecInteractor;
use UseCase\Office\LookupHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\LookupOfficeGroupInteractor;
use UseCase\Office\LookupOfficeGroupUseCase;
use UseCase\Office\LookupOfficeInteractor;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\Office\LookupVisitingCareForPwsdCalcSpecInteractor;
use UseCase\Office\LookupVisitingCareForPwsdCalcSpecUseCase;

/**
 * Office Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class OfficeDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            BulkEditOfficeGroupUseCase::class => BulkEditOfficeGroupInteractor::class,
            CreateHomeHelpServiceCalcSpecUseCase::class => CreateHomeHelpServiceCalcSpecInteractor::class,
            CreateHomeVisitLongTermCareCalcSpecUseCase::class => CreateHomeVisitLongTermCareCalcSpecInteractor::class,
            CreateOfficeGroupUseCase::class => CreateOfficeGroupInteractor::class,
            CreateOfficeUseCase::class => CreateOfficeInteractor::class,
            CreateVisitingCareForPwsdCalcSpecUseCase::class => CreateVisitingCareForPwsdCalcSpecInteractor::class,
            DeleteOfficeGroupUseCase::class => DeleteOfficeGroupInteractor::class,
            EditHomeHelpServiceCalcSpecUseCase::class => EditHomeHelpServiceCalcSpecInteractor::class,
            EditHomeVisitLongTermCareCalcSpecUseCase::class => EditHomeVisitLongTermCareCalcSpecInteractor::class,
            EditOfficeGroupUseCase::class => EditOfficeGroupInteractor::class,
            EditOfficeUseCase::class => EditOfficeInteractor::class,
            EditVisitingCareForPwsdCalcSpecUseCase::class => EditVisitingCareForPwsdCalcSpecInteractor::class,
            EnsureOfficeUseCase::class => EnsureOfficeInteractor::class,
            FindHomeHelpServiceCalcSpecUseCase::class => FindHomeHelpServiceCalcSpecInteractor::class,
            FindHomeVisitLongTermCareCalcSpecUseCase::class => FindHomeVisitLongTermCareCalcSpecInteractor::class,
            FindOfficeGroupUseCase::class => FindOfficeGroupInteractor::class,
            FindOfficeUseCase::class => FindOfficeInteractor::class,
            FindVisitingCareForPwsdCalcSpecUseCase::class => FindVisitingCareForPwsdCalcSpecInteractor::class,
            GetHomeVisitLongTermCareCalcSpecUseCase::class => GetHomeVisitLongTermCareCalcSpecInteractor::class,
            GetIndexOfficeGroupOptionUseCase::class => GetIndexOfficeGroupOptionInteractor::class,
            GetIndexOfficeOptionUseCase::class => GetIndexOfficeOptionInteractor::class,
            GetIndexOfficeUseCase::class => GetIndexOfficeInteractor::class,
            GetOfficeInfoUseCase::class => GetOfficeInfoInteractor::class,
            GetOfficeListUseCase::class => GetOfficeListInteractor::class,
            HomeHelpServiceCalcSpecFinder::class => HomeHelpServiceCalcSpecFinderEloquentImpl::class,
            HomeHelpServiceCalcSpecRepository::class => HomeHelpServiceCalcSpecRepositoryEloquentImpl::class,
            HomeVisitLongTermCareCalcSpecFinder::class => HomeVisitLongTermCareCalcSpecFinderEloquentImpl::class,
            HomeVisitLongTermCareCalcSpecRepository::class => HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl::class,
            IdentifyHomeHelpServiceCalcSpecUseCase::class => IdentifyHomeHelpServiceCalcSpecInteractor::class,
            IdentifyHomeVisitLongTermCareCalcSpecUseCase::class => IdentifyHomeVisitLongTermCareCalcSpecInteractor::class,
            IdentifyVisitingCareForPwsdCalcSpecUseCase::class => IdentifyVisitingCareForPwsdCalcSpecInteractor::class,
            ImportOfficeUseCase::class => ImportOfficeInteractor::class,
            LookupHomeHelpServiceCalcSpecUseCase::class => LookupHomeHelpServiceCalcSpecInteractor::class,
            LookupHomeVisitLongTermCareCalcSpecUseCase::class => LookupHomeVisitLongTermCareCalcSpecInteractor::class,
            LookupOfficeGroupUseCase::class => LookupOfficeGroupInteractor::class,
            LookupOfficeUseCase::class => LookupOfficeInteractor::class,
            LookupVisitingCareForPwsdCalcSpecUseCase::class => LookupVisitingCareForPwsdCalcSpecInteractor::class,
            OfficeFinder::class => OfficeFinderEloquentImpl::class,
            OfficeGroupFinder::class => OfficeGroupFinderEloquentImpl::class,
            OfficeGroupRepository::class => OfficeGroupRepositoryEloquentImpl::class,
            OfficeRepository::class => OfficeRepositoryEloquentImpl::class,
            VisitingCareForPwsdCalcSpecFinder::class => VisitingCareForPwsdCalcSpecFinderEloquentImpl::class,
            VisitingCareForPwsdCalcSpecRepository::class => VisitingCareForPwsdCalcSpecRepositoryEloquentImpl::class,
        ];
    }
}

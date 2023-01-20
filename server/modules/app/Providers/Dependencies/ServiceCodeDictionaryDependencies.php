<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryRepository;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryFinder;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepository;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepository;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository;
use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinderImpl;
use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryRepositoryEloquentImpl;
use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryFinderEloquentImpl;
use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepositoryEloquentImpl;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinderImpl;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImpl;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinderEloquentImpl;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinderImpl;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepositoryEloquentImpl;
use UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor;
use UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase;
use UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor;
use UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase;
use UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor;
use UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase;
use UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryInteractor;
use UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryUseCase;
use UseCase\ServiceCodeDictionary\IdentifyDwsVisitingCareForPwsdDictionaryInteractor;
use UseCase\ServiceCodeDictionary\IdentifyDwsVisitingCareForPwsdDictionaryUseCase;
use UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor;
use UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase;
use UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryInteractor;
use UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryUseCase;
use UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryInteractor;
use UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryUseCase;
use UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryInteractor;
use UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryUseCase;
use UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesInteractor;
use UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesInteractor;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase;

/**
 * ServiceCodeDictionary Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class ServiceCodeDictionaryDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        // Infrastructure
        yield from [
            DwsHomeHelpServiceDictionaryEntryFinder::class => DwsHomeHelpServiceDictionaryEntryFinderImpl::class,
            DwsHomeHelpServiceDictionaryEntryRepository::class => DwsHomeHelpServiceDictionaryEntryRepositoryEloquentImpl::class,
            DwsHomeHelpServiceDictionaryFinder::class => DwsHomeHelpServiceDictionaryFinderEloquentImpl::class,
            DwsHomeHelpServiceDictionaryRepository::class => DwsHomeHelpServiceDictionaryRepositoryEloquentImpl::class,
            DwsVisitingCareForPwsdDictionaryEntryFinder::class => DwsVisitingCareForPwsdDictionaryEntryFinderImpl::class,
            DwsVisitingCareForPwsdDictionaryEntryRepository::class => DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImpl::class,
            DwsVisitingCareForPwsdDictionaryFinder::class => DwsVisitingCareForPwsdDictionaryFinderEloquentImpl::class,
            DwsVisitingCareForPwsdDictionaryRepository::class => DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl::class,
            LtcsHomeVisitLongTermCareDictionaryEntryFinder::class => LtcsHomeVisitLongTermCareDictionaryEntryFinderImpl::class,
            LtcsHomeVisitLongTermCareDictionaryEntryRepository::class => LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl::class,
            LtcsHomeVisitLongTermCareDictionaryFinder::class => LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl::class,
            LtcsHomeVisitLongTermCareDictionaryRepository::class => LtcsHomeVisitLongTermCareDictionaryRepositoryEloquentImpl::class,
        ];
        // UseCase
        yield from [
            FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class => FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor::class,
            GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class => GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor::class,
            GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class => GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor::class,
            IdentifyDwsHomeHelpServiceDictionaryUseCase::class => IdentifyDwsHomeHelpServiceDictionaryInteractor::class,
            IdentifyDwsVisitingCareForPwsdDictionaryUseCase::class => IdentifyDwsVisitingCareForPwsdDictionaryInteractor::class,
            IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase::class => IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor::class,
            ImportDwsHomeHelpServiceDictionaryUseCase::class => ImportDwsHomeHelpServiceDictionaryInteractor::class,
            ImportDwsVisitingCareForPwsdDictionaryUseCase::class => ImportDwsVisitingCareForPwsdDictionaryInteractor::class,
            ImportLtcsHomeVisitLongTermCareDictionaryUseCase::class => ImportLtcsHomeVisitLongTermCareDictionaryInteractor::class,
            ResolveDwsNameFromServiceCodesUseCase::class => ResolveDwsNameFromServiceCodesInteractor::class,
            ResolveLtcsNameFromServiceCodesUseCase::class => ResolveLtcsNameFromServiceCodesInteractor::class,
        ];
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryFinderMixin;
use Tests\Unit\Mixins\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
use Tests\Unit\Mixins\IdentifyHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor} Test.
 */
class GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsHomeHelpServiceDictionaryFinderMixin;
    use ExamplesConsumer;
    use FindLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
    use IdentifyHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;

    private GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor $interactor;
    private array $filterParams;
    private array $paginationParams = ['all' => false, 'itemsPerPage' => 10];
    private FinderResult $calcSpecResult;
    private FinderResult $entryResult;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractorTest $self): void {
            $self->filterParams = $self->filterParams();
            $self->calcSpecResult = FinderResult::from(
                $self->examples->homeVisitLongTermCareCalcSpecs,
                Pagination::create()
            );
            $self->entryResult = FinderResult::from(
                $self->examples->ltcsHomeVisitLongTermCareDictionaryEntries,
                Pagination::create()
            );
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->identifyHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->homeVisitLongTermCareCalcSpecs[0]))
                ->byDefault();
            $self->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->allows('handle')
                ->andReturn($self->entryResult)
                ->byDefault();

            $self->interactor = app(GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use OfficeRepository', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->with($this->filterParams['officeId'])
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->interactor->handle($this->context, $this->filterParams);
        });
        $this->should('return FinderResult with empty seq when OfficeRepository return empty', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->with($this->filterParams['officeId'])
                ->andReturn(Seq::emptySeq());
            $result = $this->interactor->handle($this->context, $this->filterParams);
            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertEmpty($result->list);
        });
        $this->should('use identifyHomeVisitLongTermCareCalcSpecUseCase', function (): void {
            $this->identifyHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0],
                    equalTo($this->filterParams['isEffectiveOn'])
                )
                ->andReturn(Option::from($this->examples->homeVisitLongTermCareCalcSpecs[0]));
            $this->interactor->handle($this->context, $this->filterParams);
        });
        $this->should('not filter with SpecifiedOfficeAddition when identifyHomeVisitLongTermCareCalcSpecUseCase return none', function (): void {
            $dictionary = $this->examples->ltcsHomeVisitLongTermCareDictionaries[0];
            $this->identifyHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0],
                    equalTo($this->filterParams['isEffectiveOn'])
                )
                ->andReturn(Option::none());
            $this->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [
                        'providedIn' => Carbon::parse($this->filterParams['isEffectiveOn']),
                    ] + $this->filterParams,
                    $this->paginationParams
                )
                ->andReturn($this->entryResult);
            $this->interactor->handle($this->context, $this->filterParams);
        });
        $this->should('use FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase', function (): void {
            $dictionary = $this->examples->ltcsHomeVisitLongTermCareDictionaries[0];
            $calcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];
            $this->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [
                        'providedIn' => Carbon::parse($this->filterParams['isEffectiveOn']),
                        'specifiedOfficeAddition' => $calcSpec->specifiedOfficeAddition,
                    ] + $this->filterParams,
                    $this->paginationParams
                )
                ->andReturn($this->entryResult);
            $this->interactor->handle($this->context, $this->filterParams);
        });
        $this->should('return FinderResult with itemsPerPage = 10 specified', function (): void {
            $dictionary = $this->examples->ltcsHomeVisitLongTermCareDictionaries[0];
            $calcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];
            $this->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [
                        'providedIn' => Carbon::parse($this->filterParams['isEffectiveOn']),
                        'specifiedOfficeAddition' => $calcSpec->specifiedOfficeAddition,
                    ] + $this->filterParams,
                    $this->paginationParams
                )
                ->andReturn($this->entryResult);

            $result = $this->interactor->handle($this->context, $this->filterParams);
            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(LtcsHomeVisitLongTermCareDictionaryEntry::class, $item);
            }
        });
    }

    /**
     * フィルターパラメーターを返す.
     *
     * @return array
     */
    private function filterParams()
    {
        return [
            'officeId' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
            'isEffectiveOn' => $this->examples->ltcsHomeVisitLongTermCareDictionaries[0]->effectivatedOn,
        ];
    }
}

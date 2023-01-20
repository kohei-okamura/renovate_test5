<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeFinderMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor;

/**
 * \UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor のテスト.
 */
final class FindLtcsHomeVisitLongTermCareDictionaryEntryInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use MockeryMixin;
    use OfficeFinderMixin;
    use UnitSupport;

    private array $filterParams = [];
    private array $paginationParams = [
        'sortBy' => 'id',
        'itemsPerPage' => 10,
        'page' => 1,
    ];
    private FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindLtcsHomeVisitLongTermCareDictionaryEntryInteractorTest $self): void {
            $self->interactor = app(FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find LtcsHomeVisitLongTermCareDictionaryEntryInteractors using LtcsHomeVisitLongTermCareDictionaryEntryInteractorFinder', function (): void {
            $pagination = Pagination::create([
                'sortBy' => $this->paginationParams['sortBy'],
                'count' => count($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries),
                'itemsPerPage' => $this->paginationParams['itemsPerPage'],
                'page' => $this->paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries, $pagination);
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->with($this->filterParams, $this->paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->with(
                    $this->filterParams,
                    ['sortBy' => 'name'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, $this->filterParams, ['sortBy' => 'name'] + $paginationParams);
        });
    }
}

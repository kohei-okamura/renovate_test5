<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\DwsAreaGrade;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsAreaGradeFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\DwsAreaGrade\FindDwsAreaGradeInteractor;

/**
 * FindDwsAreaGradeInteractor のテスト.
 */
final class FindDwsAreaGradeInteractorTest extends Test
{
    use ContextMixin;
    use DwsAreaGradeFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private FindDwsAreaGradeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsAreaGradeInteractorTest $self): void {
            $self->interactor = app(FindDwsAreaGradeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find dwsAreaGrades using DwsAreaGradeFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'id',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'id',
                'count' => count($this->examples->dwsAreaGrades),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->dwsAreaGrades, $pagination);
            $this->dwsAreaGradeFinder
                ->expects('find')
                ->with(
                    $filterParams,
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, [], $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $paginationParams = [
                'itemPerPage' => 10,
                'page' => 2,
            ];
            $this->dwsAreaGradeFinder
                ->expects('find')
                ->with(
                    [],
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, [], $paginationParams);
        });
    }
}

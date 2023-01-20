<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsAreaGrade;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsAreaGradeFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\LtcsAreaGrade\FindLtcsAreaGradeInteractor;

/**
 * FindLtcsAreaGradeInteractor のテスト.
 */
final class FindLtcsAreaGradeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsAreaGradeFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private FindLtcsAreaGradeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindLtcsAreaGradeInteractorTest $self): void {
            $self->interactor = app(FindLtcsAreaGradeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find LtcsAreaGrades using LtcsAreaGradeFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->ltcsAreaGrades),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->ltcsAreaGrades, $pagination);
            $this->ltcsAreaGradeFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = ['itemPerPage' => 10, 'page' => 2];
            $this->ltcsAreaGradeFinder
                ->expects('find')
                ->with($filterParams, ['sortBy' => 'id'] + $paginationParams)
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, $filterParams, $paginationParams);
        });
    }
}

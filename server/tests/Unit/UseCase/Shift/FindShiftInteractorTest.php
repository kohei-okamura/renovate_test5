<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftFinderMixin;
use Tests\Unit\Test;
use UseCase\Shift\FindShiftInteractor;

/**
 * FindShiftInteractor のテスト.
 */
final class FindShiftInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use ShiftFinderMixin;
    use UnitSupport;

    private FindShiftInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindShiftInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();

            $self->interactor = app(FindShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find Shift using ShiftFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::create([
                'list' => $this->examples->shifts,
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listShifts(), [], $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->shiftFinder
                ->expects('find')
                ->with(
                    $filterParams + ['organizationId' => $this->context->organization->id],
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(
                    FinderResult::create([
                        'list' => [],
                        'pagination' => Pagination::create(),
                    ])
                );

            $this->interactor->handle($this->context, Permission::listShifts(), [], $paginationParams);
        });
    }
}

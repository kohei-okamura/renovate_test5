<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
use Tests\Unit\Mixins\AttendanceFinderMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Shift\FindAttendanceInteractor;

/**
 * {@link \UseCase\Shift\FindAttendanceInteractor} のテスト.
 */
final class FindAttendanceInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use AttendanceFinderMixin;
    use UnitSupport;

    private FindAttendanceInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (FindAttendanceInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();

            $self->interactor = app(FindAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find Attendance using AttendanceFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->attendances),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::create([
                'list' => $this->examples->attendances,
                'pagination' => $pagination,
            ]);
            $this->attendanceFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listAttendances(), [], $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->attendanceFinder
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

            $this->interactor->handle($this->context, Permission::listAttendances(), [], $paginationParams);
        });
    }
}

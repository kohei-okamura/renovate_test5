<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Permission;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\PermissionGroupFinderMixin;
use Tests\Unit\Test;
use UseCase\Permission\FindPermissionGroupInteractor;

/**
 * \UseCase\Permission\FindPermissionGroupInteractor のテスト.
 */
final class FindPermissionGroupInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use PermissionGroupFinderMixin;
    use UnitSupport;

    private FindPermissionGroupInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindPermissionGroupInteractorTest $self): void {
            $self->interactor = app(FindPermissionGroupInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find permissionGroups using PermissionGroupFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'sort_order',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([]);
            $expected = FinderResult::from($this->examples->permissionGroups, $pagination);
            $this->permissionGroupFinder->expects('find')->with($filterParams, $paginationParams)->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [];
            $this->permissionGroupFinder
                ->expects('find')
                ->with($filterParams, ['sortBy' => 'sortOrder'] + $paginationParams)
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, $filterParams, $paginationParams);
        });
    }
}

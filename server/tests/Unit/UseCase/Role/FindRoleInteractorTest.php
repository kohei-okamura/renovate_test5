<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Role;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RoleFinderMixin;
use Tests\Unit\Test;
use UseCase\Role\FindRoleInteractor;

/**
 * \UseCase\Role\FindRoleInteractor のテスト.
 */
final class FindRoleInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RoleFinderMixin;
    use UnitSupport;

    private FindRoleInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindRoleInteractorTest $self): void {
            $self->interactor = app(FindRoleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find Roles using RoleFinder', function (): void {
            $filterParams = ['organizationId' => $this->examples->roles[0]->organizationId];
            $paginationParams = [
                'sortBy' => 'sortOrder',
                'itemsPerPage' => 10,
                'page' => 1,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'sortOrder',
                'count' => count($this->examples->roles),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->roles, $pagination);
            $this->roleFinder->expects('find')->with($filterParams, $paginationParams)->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = ['organizationId' => $this->examples->roles[0]->organizationId];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->roleFinder
                ->expects('find')
                ->with($filterParams, ['sortBy' => 'sortOrder'] + $paginationParams)
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, $filterParams, $paginationParams);
        });
    }
}

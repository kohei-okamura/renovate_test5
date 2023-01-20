<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProjectFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Project\FindDwsProjectInteractor;

/**
 * \UseCase\DwsProject\FindDwsProjectInteractor のテスト.
 */
final class FindDwsProjectInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use DwsProjectFinderMixin;
    use UnitSupport;

    private FindDwsProjectInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsProjectInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();

            $self->interactor = app(FindDwsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find DwsProjects using DwsProjectFinder', function (): void {
            $filterParams = ['organizationId' => $this->examples->dwsProjects[0]->organizationId];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->dwsProjects),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->dwsProjects, $pagination);
            $this->dwsProjectFinder->expects('find')->with($filterParams, $paginationParams)->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listDwsProjects(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = ['organizationId' => $this->examples->dwsProjects[0]->organizationId];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->dwsProjectFinder
                ->expects('find')
                ->with($filterParams, ['sortBy' => 'date'] + $paginationParams)
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listDwsProjects(), $filterParams, $paginationParams);
        });
    }
}

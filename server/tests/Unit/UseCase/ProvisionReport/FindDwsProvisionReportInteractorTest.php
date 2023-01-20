<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\FindDwsProvisionReportInteractor;

/**
 * {@link \UseCase\ProvisionReport\FindDwsProvisionReportInteractor} のテスト.
 */
final class FindDwsProvisionReportInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use DwsProvisionReportFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private FindDwsProvisionReportInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsProvisionReportInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();

            $self->interactor = app(FindDwsProvisionReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find DwsProvisionReports using DwsProvisionReportFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->dwsProvisionReports),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->dwsProvisionReports, $pagination);
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listDwsProvisionReports(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with(
                    $filterParams,
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listDwsProvisionReports(), $filterParams, $paginationParams);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\HomeVisitLongTermCareCalcSpecFinderMixin;
use Tests\Unit\Test;
use UseCase\Office\FindHomeVisitLongTermCareCalcSpecInteractor;

/**
 * {@link \UseCase\Office\FindHomeVisitLongTermCareCalcSpecInteractor} Test.
 */
class FindHomeVisitLongTermCareCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use HomeVisitLongTermCareCalcSpecFinderMixin;
    use UnitSupport;

    private FindHomeVisitLongTermCareCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindHomeVisitLongTermCareCalcSpecInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();
            $self->interactor = app(FindHomeVisitLongTermCareCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find HomeVisitLongTermCareCalcSpec using HomeVisitLongTermCareCalcSpecFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->homeVisitLongTermCareCalcSpecs),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->homeVisitLongTermCareCalcSpecs, $pagination);
            $this->homeVisitLongTermCareCalcSpecFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listInternalOffices(), $filterParams, $paginationParams)
            );
        });
        $this->should('not use default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->homeVisitLongTermCareCalcSpecFinder
                ->expects('find')
                ->with(
                    $filterParams + ['organizationId' => $this->context->organization->id],
                    ['sortBy' => ''] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listInternalOffices(), $filterParams, $paginationParams);
        });
    }
}

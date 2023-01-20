<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\OwnExpenseProgram;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OwnExpenseProgramFinderMixin;
use Tests\Unit\Test;
use UseCase\OwnExpenseProgram\FindOwnExpenseProgramInteractor;

/**
 * {@link \UseCase\OwnExpenseProgram\FindOwnExpenseProgramInteractor} のテスト.
 */
class FindOwnExpenseProgramInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use OwnExpenseProgramFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private FindOwnExpenseProgramInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindOwnExpenseProgramInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();

            $self->interactor = app(FindOwnExpenseProgramInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find OwnExpensePrograms using OwnExpenseProgramFinder', function (): void {
            $filterParams = ['organizationId' => $this->examples->ownExpensePrograms[0]->organizationId];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->ownExpensePrograms),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->ownExpensePrograms, $pagination);
            $this->ownExpenseProgramFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listOwnExpensePrograms(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = ['organizationId' => $this->examples->ownExpensePrograms[0]->organizationId];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->ownExpenseProgramFinder
                ->expects('find')
                ->with(
                    $filterParams,
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listOwnExpensePrograms(), $filterParams, $paginationParams);
        });
        $this->should('filter by officeIdsOrNull when getPermittedOffices return some', function (): void {
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::from(Seq::from($this->examples->offices[0])))
                ->byDefault();

            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->ownExpenseProgramFinder
                ->expects('find')
                ->with(
                    [
                        'officeIdsOrNull' => [$this->examples->offices[0]->id],
                        'organizationId' => $this->examples->ownExpensePrograms[0]->organizationId,
                    ],
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listOwnExpensePrograms(), $filterParams, $paginationParams);
        });
    }
}

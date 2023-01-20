<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserDwsCalcSpecFinderMixin;
use Tests\Unit\Test;
use UseCase\User\FindUserDwsCalcSpecInteractor;

/**
 * {@link \UseCase\User\FindUserDwsCalcSpecInteractor} Test.
 */
class FindUserDwsCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use UserDwsCalcSpecFinderMixin;

    private FindUserDwsCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindUserDwsCalcSpecInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();
            $self->interactor = app(FindUserDwsCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find dwsCalcSpec using Finder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->userDwsCalcSpecs),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->userDwsCalcSpecs, $pagination);
            $this->userDwsCalcSpecFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listUsers(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->userDwsCalcSpecFinder
                ->expects('find')
                ->with(
                    $filterParams,
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listUsers(), $filterParams, $paginationParams);
        });
    }
}

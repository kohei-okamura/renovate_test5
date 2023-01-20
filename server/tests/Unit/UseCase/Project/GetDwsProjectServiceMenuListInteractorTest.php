<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProjectServiceMenuFinderMixin;
use Tests\Unit\Mixins\GetDwsProjectServiceMenuListUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Project\GetDwsProjectServiceMenuListInteractor;

/**
 * {@link \UseCase\Project\GetDwsProjectServiceMenuListInteractor} のテスト.
 */
class GetDwsProjectServiceMenuListInteractorTest extends Test
{
    use ContextMixin;
    use DwsProjectServiceMenuFinderMixin;
    use ExamplesConsumer;
    use GetDwsProjectServiceMenuListUseCaseMixin;
    use UnitSupport;

    private GetDwsProjectServiceMenuListInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetDwsProjectServiceMenuListInteractorTest $self): void {
            $self->dwsProjectServiceMenuFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsProjectServiceMenus, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetDwsProjectServiceMenuListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return FinderResult of DwsProjectServiceMenu', function (): void {
            $this->dwsProjectServiceMenuFinder
                ->expects('find')
                ->with([], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from($this->examples->dwsProjectServiceMenus, Pagination::create()));

            $this->assertModelStrictEquals(
                FinderResult::from($this->examples->dwsProjectServiceMenus, Pagination::create()),
                $this->interactor->handle($this->context, true)
            );
        });
        $this->should('use DwsProjectServiceMenuFinder', function (): void {
            $this->dwsProjectServiceMenuFinder
                ->expects('find')
                ->with([], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from($this->examples->dwsProjectServiceMenus, Pagination::create()));

            $this->interactor->handle($this->context, true);
        });
    }
}

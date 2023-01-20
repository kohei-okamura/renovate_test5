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
use Tests\Unit\Mixins\GetLtcsProjectServiceMenuListUseCaseMixin;
use Tests\Unit\Mixins\LtcsProjectServiceMenuFinderMixin;
use Tests\Unit\Test;
use UseCase\Project\GetLtcsProjectServiceMenuListInteractor;

/**
 * {@link \UseCase\Project\GetLtcsProjectServiceMenuListInteractor} のテスト.
 */
class GetLtcsProjectServiceMenuListInteractorTest extends Test
{
    use ContextMixin;
    use LtcsProjectServiceMenuFinderMixin;
    use ExamplesConsumer;
    use GetLtcsProjectServiceMenuListUseCaseMixin;
    use UnitSupport;

    private GetLtcsProjectServiceMenuListInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetLtcsProjectServiceMenuListInteractorTest $self): void {
            $self->ltcsProjectServiceMenuFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsProjectServiceMenus, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetLtcsProjectServiceMenuListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return FinderResult of LtcsProjectServiceMenu', function (): void {
            $this->ltcsProjectServiceMenuFinder
                ->expects('find')
                ->with([], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from($this->examples->ltcsProjectServiceMenus, Pagination::create()));

            $this->assertModelStrictEquals(
                FinderResult::from($this->examples->ltcsProjectServiceMenus, Pagination::create()),
                $this->interactor->handle($this->context, true)
            );
        });
        $this->should('use LtcsProjectServiceMenuFinder', function (): void {
            $this->ltcsProjectServiceMenuFinder
                ->expects('find')
                ->with([], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from($this->examples->ltcsProjectServiceMenus, Pagination::create()));

            $this->interactor->handle($this->context, true);
        });
    }
}

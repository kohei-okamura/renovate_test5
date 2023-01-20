<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsInsCard;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsInsCardFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\LtcsInsCard\FindLtcsInsCardInteractor;

/**
 * {@link \UseCase\LtcsInsCard\FindLtcsInsCardInteractor} のテスト.
 */
final class FindLtcsInsCardInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsInsCardFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private FindLtcsInsCardInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();
            $self->interactor = app(FindLtcsInsCardInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find ltcsInsCards using Finder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->users),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->ltcsAreaGrades, $pagination);
            $this->ltcsInsCardFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], $paginationParams)
                ->andReturn($expected);

            $actual = $this->interactor->handle(
                $this->context,
                Permission::listLtcsInsCards(),
                $filterParams,
                $paginationParams
            );

            $this->assertModelStrictEquals($expected, $actual);
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->ltcsInsCardFinder
                ->expects('find')
                ->with(
                    $filterParams + ['organizationId' => $this->context->organization->id],
                    ['sortBy' => 'date'] + $paginationParams
                )
                ->andReturn(FinderResult::from([$this->context->organization->id], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listLtcsInsCards(), $filterParams, $paginationParams);
        });
    }
}

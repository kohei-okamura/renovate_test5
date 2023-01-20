<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\DwsCertification;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsCertificationFinderMixin;
use Tests\Unit\Test;
use UseCase\DwsCertification\FindDwsCertificationInteractor;

/**
 * {@link \UseCase\DwsCertification\FindDwsCertificationInteractor} のテスト.
 */
class FindDwsCertificationInteractorTest extends Test
{
    use ContextMixin;
    use DwsCertificationFinderMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private FindDwsCertificationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsCertificationInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();
            $self->interactor = app(FindDwsCertificationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find dwsCertification using Finder', function (): void {
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
            $expected = FinderResult::from($this->examples->dwsCertifications, $pagination);
            $this->dwsCertificationFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle(
                    $this->context,
                    Permission::listDwsCertifications(),
                    $filterParams,
                    $paginationParams
                )
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->dwsCertificationFinder
                ->expects('find')
                ->with(
                    $filterParams + ['organizationId' => $this->context->organization->id],
                    ['sortBy' => 'date'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle(
                $this->context,
                Permission::listDwsCertifications(),
                $filterParams,
                $paginationParams
            );
        });
    }
}

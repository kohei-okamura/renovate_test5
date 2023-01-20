<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Role\RoleScope;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupFinderMixin;
use Tests\Unit\Test;
use UseCase\Office\FindOfficeGroupInteractor;

/**
 * FindOfficeGroupInteractor のテスト.
 */
final class FindOfficeGroupInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeGroupFinderMixin;
    use UnitSupport;

    private FindOfficeGroupInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindOfficeGroupInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();
            $self->context
                ->allows('hasRoleScope')
                ->andReturn(true)
                ->byDefault();
            $self->interactor = app(FindOfficeGroupInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find OfficeGroup using OfficeGroupFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->officeGroups),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->officeGroups, $pagination);
            $this->officeGroupFinder
                ->expects('find')
                ->with(
                    $filterParams + [
                        'organizationId' => $this->context->organization->id,
                    ],
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, [], $paginationParams)
            );
        });
        $this->should('filter with ids when RoleScope is group', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->officeGroups),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->officeGroups, $pagination);
            $this->context
                ->allows('hasRoleScope')
                ->with(RoleScope::whole())
                ->andReturn(false);
            $this->officeGroupFinder
                ->expects('find')
                ->with(
                    $filterParams + [
                        'ids' => $this->examples->staffs[0]->officeGroupIds,
                        'organizationId' => $this->context->organization->id,
                    ],
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, [], $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->officeGroupFinder
                ->expects('find')
                ->with(
                    $filterParams + [
                        'organizationId' => $this->context->organization->id,
                    ],
                    ['sortBy' => 'sortOrder'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, [], $paginationParams);
        });
        $this->should('return FinderResult with empty when RoleScope is office', function (): void {
            $this->context
                ->allows('hasRoleScope')
                ->andReturn(false);

            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->assertModelStrictEquals(
                FinderResult::from([], Pagination::create([
                    'count' => 0,
                    'desc' => false,
                    'itemsPerPage' => $paginationParams['itemsPerPage'],
                    'page' => $paginationParams['page'],
                    'pages' => 1,
                    'sortBy' => 'sortOrder',
                ])),
                $this->interactor->handle($this->context, [], $paginationParams)
            );
        });
    }
}

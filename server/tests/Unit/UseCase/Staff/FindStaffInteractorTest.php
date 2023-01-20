<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffFinderMixin;
use Tests\Unit\Test;
use UseCase\Staff\FindStaffInteractor;

/**
 * \UseCase\Staff\FindStaffInteractor のテスト.
 */
final class FindStaffInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffFinderMixin;
    use UnitSupport;

    private FindStaffInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindStaffInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::from(Seq::from($self->examples->offices[0])))
                ->byDefault();
            $self->interactor = app(FindStaffInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find staffs using StaffFinder', function (): void {
            $filterParams = ['organizationId' => $this->examples->staffs[0]->organizationId];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->staffs),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->staffs, $pagination);
            $this->staffFinder
                ->expects('find')->with(
                    ['officeIds' => [$this->examples->offices[0]->id]] + $filterParams,
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listStaffs(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = ['organizationId' => $this->examples->staffs[0]->organizationId];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->staffFinder
                ->expects('find')
                ->with(
                    ['officeIds' => [$this->examples->offices[0]->id]] + $filterParams,
                    ['sortBy' => 'name'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listStaffs(), $filterParams, $paginationParams);
        });
        $this->should(
            'filter by officeIds when specify officeIds and getPermittedOffices return none',
            function (): void {
                $this->context
                    ->expects('getPermittedOffices')
                    ->andReturn(Option::none())
                    ->byDefault();
                $filterParams = [
                    'officeIds' => [$this->examples->offices[0]->id, $this->examples->offices[1]->id],
                ];
                $paginationParams = [
                    'itemsPerPage' => 10,
                    'page' => 2,
                ];
                $this->staffFinder
                    ->expects('find')
                    ->with(
                        $filterParams + [
                            'organizationId' => $this->context->organization->id,
                        ],
                        ['sortBy' => 'name'] + $paginationParams
                    )
                    ->andReturn(FinderResult::from([], Pagination::create()));

                $this->interactor->handle($this->context, Permission::listStaffs(), $filterParams, $paginationParams);
            }
        );
        $this->should(
            'filter by permitted Offices and specified officeIds when specify officeIds and getPermittedOffices return some',
            function (): void {
                $this->context
                    ->expects('getPermittedOffices')
                    ->andReturn(Option::from(Seq::from($this->examples->offices[0], $this->examples->offices[1])))
                    ->byDefault();
                $filterParams = [
                    'officeIds' => [$this->examples->offices[1]->id, $this->examples->offices[2]->id],
                ];
                $paginationParams = [
                    'itemsPerPage' => 10,
                    'page' => 2,
                ];
                $this->staffFinder
                    ->expects('find')
                    ->with(
                        [
                            'officeIds' => [$this->examples->offices[1]->id],
                            'organizationId' => $this->context->organization->id,
                        ],
                        ['sortBy' => 'name'] + $paginationParams
                    )
                    ->andReturn(FinderResult::from([], Pagination::create()));

                $this->interactor->handle($this->context, Permission::listStaffs(), $filterParams, $paginationParams);
            }
        );
        $this->should(
            'return empty list when no overlap between permitted Offices and specified officeIds',
            function (): void {
                $this->context
                    ->expects('getPermittedOffices')
                    ->andReturn(Option::from(Seq::from($this->examples->offices[0], $this->examples->offices[1])))
                    ->byDefault();
                $filterParams = [
                    'officeIds' => [$this->examples->offices[2]->id, $this->examples->offices[3]->id],
                ];
                $paginationParams = [
                    'itemsPerPage' => 10,
                    'page' => 2,
                ];

                $this->assertTrue(
                    $this->interactor
                        ->handle($this->context, Permission::listStaffs(), $filterParams, $paginationParams)
                        ->list
                        ->isEmpty()
                );
            }
        );
    }
}

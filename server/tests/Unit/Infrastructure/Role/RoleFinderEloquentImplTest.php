<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Role;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Role\Role;
use Infrastructure\Role\RoleFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * RoleFinderEloquentImpl のテスト.
 */
class RoleFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private RoleFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (RoleFinderEloquentImplTest $self): void {
            $self->finder = app(RoleFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of Role', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'name']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Role::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->roles);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'sortOrder',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->roles);
                $this->assertNotEmpty($result->list);
                $this->assertSame($itemsPerPage, $result->pagination->itemsPerPage);
                $this->assertSame($page, $result->pagination->page);
                $this->assertSame($pages, $result->pagination->pages);
                $this->assertSame($count, $result->pagination->count);
            },
            [
                'examples' => [
                    'all is not given' => [
                        [],
                    ],
                    'all is false' => [
                        ['all' => false],
                    ],
                    'all is 0' => [
                        ['all' => 0],
                    ],
                ],
            ]
        );
        $this->should(
            'return a FinderResult with only one page when param `all` given and truthy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->roles);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'sortOrder',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->roles);
                $this->assertNotEmpty($result->list);
                $this->assertSame($count, $result->pagination->count);
                $this->assertSame($count, $result->pagination->itemsPerPage);
                $this->assertSame(1, $result->pagination->page);
                $this->assertSame(1, $result->pagination->pages);
            },
            [
                'examples' => [
                    'all is true' => [
                        ['all' => true],
                    ],
                    'all is 1' => [
                        ['all' => 1],
                    ],
                ],
            ]
        );
        $this->should(
            'return a FinderResult of roles with given `organizationId`',
            function (): void {
                $result = $this->finder->find(
                    ['organizationId' => $this->examples->organizations[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'sortOrder',
                    ]
                );

                $this->assertNotEmpty($this->examples->roles);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Role $x): bool => $x->organizationId === $this->examples->organizations[0]->id
                );
                $this->assertExists(
                    $this->examples->roles,
                    fn (Role $x): bool => $x->organizationId !== $this->examples->organizations[0]->id
                );
            }
        );
        $this->should(
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $this->finder->find(
                            ['organizationId' => $this->examples->organizations[0]->id],
                            ['all' => true]
                        );
                    }
                );
            }
        );
        $this->should(
            'sort roles using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->roles)
                    ->filter(fn (Role $role) => $role->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy(fn (Role $role) => $role->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->find($filterParams, $paginationParams)->list as $index => $role) {
                    $this->assertModelStrictEquals($expected[$index], $role);
                }
            }
        );
    }
}

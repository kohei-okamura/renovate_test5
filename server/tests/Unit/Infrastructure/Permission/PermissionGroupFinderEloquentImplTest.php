<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Permission;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\PermissionGroup;
use Infrastructure\Permission\PermissionGroupFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * PermissionGroupFinderEloquentImpl のテスト
 */
class PermissionGroupFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private PermissionGroupFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (PermissionGroupFinderEloquentImplTest $self): void {
            $self->finder = app(PermissionGroupFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of PermissionGroup', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'sortOrder']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(PermissionGroup::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->permissionGroups);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'sortOrder',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->permissionGroups);
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
                $page = 2;
                $count = count($this->examples->permissionGroups);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'sortOrder',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->permissionGroups);
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
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $this->finder->find(
                            [],
                            ['all' => true]
                        );
                    }
                );
            }
        );
        $this->should(
            'sort PermissionGroups using given param `sortBy` and `desc`',
            function (string $sortBy, string $orderColumn): void {
                $expected = Seq::fromArray($this->examples->permissionGroups)
                    ->sortBy(fn (PermissionGroup $permissionGroup) => $permissionGroup->{$orderColumn})
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => $sortBy,
                ];

                $result = $this->finder->find([], $paginationParams);

                $this->assertEach(
                    function ($a, $b): void {
                        $this->assertModelStrictEquals($a, $b);
                    },
                    $expected,
                    $result->list->toArray()
                );
            },
            [
                'examples' => [
                    'sort By id' => [
                        'id',
                        'id',
                    ],
                    'sort By sortOrder' => [
                        'sortOrder',
                        'sortOrder',
                    ],
                ],
            ]
        );
    }
}

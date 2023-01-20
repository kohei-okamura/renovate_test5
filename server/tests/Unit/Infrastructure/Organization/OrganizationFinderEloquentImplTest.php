<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Organization;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Organization\Organization;
use Infrastructure\Organization\OrganizationFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * OrganizationFinderEloquentImpl のテスト.
 */
class OrganizationFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private OrganizationFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (OrganizationFinderEloquentImplTest $self): void {
            $self->finder = app(OrganizationFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of Organization', function (): void {
            $result = $this->finder->find([], ['all' => true, 'sortBy' => 'id']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Organization::class, $item);
            }
        });
        $this->should('return a FinderResult when itemsPerPage is 1', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->organizations[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->organizations);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'id',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($result);
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
                $count = count($this->examples->organizations);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'id',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($result);
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
            'return a filtered FinderResult when filterParam is specified',
            function ($filter): void {
                $result = $this->finder->find(
                    [$filter[0] => $filter[1]],
                    ['all' => true, 'sortBy' => 'id']
                );

                $this->assertInstanceOf(FinderResult::class, $result);
                $this->assertInstanceOf(Seq::class, $result->list);
                $this->assertInstanceOf(Pagination::class, $result->pagination);
                $this->assertNotEmpty($result->list);
                foreach ($result->list as $item) {
                    $this->assertInstanceOf(Organization::class, $item);
                    $this->assertSame($filter[1], $item->{$filter[0]});
                }
            },
            [
                'examples' => [
                    'isEnabled is true' => [
                        ['isEnabled', true],
                    ],
                    'version is 1' => [
                        ['version', 1],
                    ],
                ],
            ]
        );
        $this->should('throw InvalidArgumentException when `sortBy` not given or empty', function (): void {
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    $this->finder->find(
                        [],
                        ['all' => true]
                    );
                }
            );
        });
    }
}

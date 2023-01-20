<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DwsAreaGrade;

use Domain\Common\Pagination;
use Domain\DwsAreaGrade\DwsAreaGrade;
use Domain\FinderResult;
use Infrastructure\DwsAreaGrade\DwsAreaGradeFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsAreaGradeFinderEloquentImpl のテスト.
 */
class DwsAreaGradeFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsAreaGradeFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsAreaGradeFinderEloquentImplTest $self): void {
            $self->finder = app(DwsAreaGradeFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsAreaGrade', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(DwsAreaGrade::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->dwsAreaGrades);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'id',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsAreaGrades);
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
                $count = count($this->examples->dwsAreaGrades);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'id',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsAreaGrades);
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
            'sort dwsAreaGrades using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->dwsAreaGrades)
                    ->sortBy(fn (DwsAreaGrade $dwsAreaGrades) => $dwsAreaGrades->id)
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'id',
                ];
                $result = $this->finder->find([], $paginationParams);
                $this->assertEach(
                    function ($a, $b): void {
                        $this->assertModelStrictEquals($a, $b);
                    },
                    $expected,
                    $result->list->toArray()
                );
            }
        );
        $this->should(
            'sort dwsAreaGrades using specified param in `sortBy`',
            function ($sortBy, $fnSortBy): void {
                $expected = Seq::fromArray($this->examples->dwsAreaGrades)
                    ->sortBy($fnSortBy)
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
                    'code specified' => [
                        'code',
                        fn (DwsAreaGrade $dwsAreaGrade) => $dwsAreaGrade->code,
                    ],
                    'name specified' => [
                        'name',
                        fn (DwsAreaGrade $dwsAreaGrade) => $dwsAreaGrade->name,
                    ],
                ],
            ]
        );
    }
}

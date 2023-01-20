<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\OfficeGroup;
use Infrastructure\Office\OfficeGroupFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * OfficeGroupFinderEloquentImpl のテスト.
 */
class OfficeGroupFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private OfficeGroupFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (OfficeGroupFinderEloquentImplTest $self): void {
            $self->finder = app(OfficeGroupFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of OfficeGroup', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'sortOrder']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(OfficeGroup::class, $item);
            }
        });
        $this->should(
            'return a FinderResult of OfficeGroup specified condition',
            function (string $key, $value, string $element): void {
                $result = $this->finder->find([$key => $value], ['all' => true, 'sortBy' => 'sortOrder']);

                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (OfficeGroup $x): bool => $x->{$element} === $value
                );
            },
            ['examples' => [
                'specified organizationId' => [
                    'organizationId',
                    $this->examples->organizations[0]->id,
                    'organizationId',
                ],
                'sepcified parentOfficeGroupId' => [
                    'parentOfficeGroupIds',
                    $this->examples->officeGroups[0]->id,
                    'parentOfficeGroupId',
                ],
            ],
            ],
        );
        $this->should('return a FinderResult of OfficeGroup specified ids', function (): void {
            $result = $this->finder->find(
                ['ids' => $this->examples->staffs[14]->officeGroupIds],
                ['all' => true, 'sortBy' => 'sortOrder']
            );
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (OfficeGroup $x): bool => in_array($x->id, $this->examples->staffs[14]->officeGroupIds, true)
            );
            $this->assertExists(
                $this->examples->officeGroups,
                fn (OfficeGroup $x): bool => !in_array($x->id, $this->examples->staffs[0]->officeGroupIds, true)
            );
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->officeGroups);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'sortOrder',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->officeGroups);
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
                $count = count($this->examples->officeGroups);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'sortOrder',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->officeGroups);
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
            'sort OfficeGroups using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->officeGroups)
                    ->sortBy(fn (OfficeGroup $officeGroups) => $officeGroups->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                foreach ($this->finder->find([], $paginationParams)->list as $index => $officeGroups) {
                    $this->assertModelStrictEquals($expected[$index], $officeGroups);
                }
            }
        );
    }
}

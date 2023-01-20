<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Project;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Domain\Project\DwsProjectServiceMenu;
use Infrastructure\Project\DwsProjectServiceMenuFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Project\DwsProjectServiceMenuFinderEloquentImpl} のテスト
 */
final class DwsProjectServiceMenuFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsProjectServiceMenuFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsProjectServiceMenuFinderEloquentImplTest $self): void {
            $self->finder = app(DwsProjectServiceMenuFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return FinderResult on DwsProjectServiceMenu', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsProjectServiceMenu);
        });

        $this->should('return DwsProjectServiceMenu Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1, 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsProjectServiceMenus[0], $result->list->head());
        });

        $this->should('return FinderResult on DwsProjectServiceMenu with given invalid filter', function (): void {
            $result = $this->finder->find(['invalid' => 1234], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsProjectServiceMenu);
        });

        $this->should('return FinderResult with sorted list', function (string $key, Closure $sortBy): void {
            $expects = Seq::fromArray($this->examples->dwsProjectServiceMenus)
                ->sortBy($sortBy);

            $result = $this->finder->find([], ['sortBy' => $key, 'desc' => false, 'all' => true]);

            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals($expects->toArray(), $result->list->toArray());
        }, [
            'examples' => [
                'sortBy date' => [
                    'date',
                    fn (DwsProjectServiceMenu $x): Carbon => $x->createdAt,
                ],
                'sortBy id' => [
                    'id',
                    fn (DwsProjectServiceMenu $x): int => $x->id,
                ],
            ],
        ]);

        $this->should(
            'return a FinderResult with only one page when param `all` given and truthy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsProjectServiceMenus);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsProjectServiceMenus);
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
    }
}

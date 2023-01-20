<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\LtcsInsCard;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\LtcsInsCard\LtcsInsCard;
use Infrastructure\LtcsInsCard\LtcsInsCardFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * LtcsInsCardFinderEloquentImpl のテスト.
 */
class LtcsInsCardFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private LtcsInsCardFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (LtcsInsCardFinderEloquentImplTest $self): void {
            $self->finder = app(LtcsInsCardFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of LtcsInsCard', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(LtcsInsCard::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->ltcsInsCards);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsInsCards);
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
                $count = count($this->examples->ltcsInsCards);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsInsCards);
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
            'sort ltcsInsCards using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->ltcsInsCards)
                    ->filter(fn (LtcsInsCard $ltcsInsCard) => $ltcsInsCard->userId === $this->examples->users[3]->id)
                    ->sortBy(fn (LtcsInsCard $ltcsInsCard) => $ltcsInsCard->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['userId' => $this->examples->users[3]->id];
                $actual = $this->finder->find($filterParams, $paginationParams);
                $this->assertEach(
                    function ($a, $b) {
                        $this->assertModelStrictEquals($a, $b);
                    },
                    $expected,
                    $actual->list->toArray()
                );
            }
        );
        $this->should('return FinderResult with sorted list', function (string $key, Closure $sortBy): void {
            $expects = Seq::fromArray($this->examples->ltcsInsCards)
                ->sortBy($sortBy);

            $result = $this->finder->find([], ['sortBy' => $key, 'desc' => false, 'all' => true]);

            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals($expects->toArray(), $result->list->toArray());
        }, [
            'examples' => [
                'sortBy effectivatedOn' => [
                    'effectivatedOn',
                    fn (LtcsInsCard $x): Carbon => $x->effectivatedOn,
                ],
                'sortBy id' => [
                    'id',
                    fn (LtcsInsCard $x): int => $x->id,
                ],
            ],
        ]);
        $this->should('return a FinderResult of ltcsInsCards with given `effectivatedBefore`', function (): void {
            $date = Carbon::parse('2020-10-10');
            $result = $this->finder->find(['effectivatedBefore' => $date], ['sortBy' => 'date']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (LtcsInsCard $x): bool => $x->effectivatedOn <= $date
            );
            $this->assertExists(
                $this->examples->ltcsInsCards,
                fn (LtcsInsCard $x): bool => $x->effectivatedOn > $date
            );
        });
        $this->should('return a FinderResult of ltcsInsCards with given `userId`', function (): void {
            $result = $this->finder->find(['userId' => $this->examples->users[3]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (LtcsInsCard $x): bool => $x->userId === $this->examples->users[3]->id
            );
            $this->assertExists(
                $this->examples->ltcsInsCards,
                fn (LtcsInsCard $x): bool => $x->userId !== $this->examples->users[3]->id
            );
        });
        $this->should('return a FinderResult of ltcsInsCards with given `userIds`', function (): void {
            $result = $this->finder->find(['userIds' => [$this->examples->users[3]->id]], ['sortBy' => 'date']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (LtcsInsCard $x): bool => $x->userId === $this->examples->users[3]->id
            );
            $this->assertExists(
                $this->examples->ltcsInsCards,
                fn (LtcsInsCard $x): bool => $x->userId !== $this->examples->users[3]->id
            );
        });
        $this->should('return a FinderResult of ltcsInsCards with invalid filter keyword', function (): void {
            $result = $this->finder->find(['userId' => $this->examples->users[3]->id, 'dummy' => 'eustylelab'], ['sortBy' => 'date']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (LtcsInsCard $x): bool => $x->userId === $this->examples->users[3]->id
            );
            $this->assertExists(
                $this->examples->ltcsInsCards,
                fn (LtcsInsCard $x): bool => $x->userId !== $this->examples->users[3]->id
            );
        });
    }
}

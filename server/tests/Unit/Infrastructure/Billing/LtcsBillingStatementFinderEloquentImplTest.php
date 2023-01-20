<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\LtcsBillingStatement;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\PoliteEntity;
use Infrastructure\Billing\LtcsBillingStatementFinderEloquentImpl;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\LtcsBillingStatementFinderEloquentImpl} のテスト.
 */
final class LtcsBillingStatementFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBillingStatementFinderEloquentImpl $finder;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->finder = app(LtcsBillingStatementFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of LtcsBillingStatement', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (PoliteEntity $x): bool => $x instanceof LtcsBillingStatement);
        });
        $this->should('return LtcsBillingStatement Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->ltcsBillingStatements[0],
                $result->list->head(),
            );
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->ltcsBillingStatements);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsBillingStatements);
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
                $count = count($this->examples->ltcsBillingStatements);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsBillingStatements);
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
        $this->should('return non-filtered list with given invalid parameter', function (): void {
            // finder はデフォルトだと 10 件取得なので、前から 10 件取得する
            $expected = Seq::fromArray($this->examples->ltcsBillingStatements)->take(10);
            $result = $this->finder->find(['invalid' => true], ['sortBy' => 'id']);

            $this->assertArrayStrictEquals($expected->toArray(), $result->list->toArray());
        });
        $this->should(
            'return a FinderResult of DwsBillingBundles with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->ltcsBillingStatements,
                    $this->invert($f)
                );
                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->ltcsBillingStatements);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'when userId' => [
                        ['userId' => $this->examples->ltcsBillingStatements[0]->user->userId],
                        function (LtcsBillingStatement $ltcsBillingStatement): bool {
                            return $ltcsBillingStatement->user->userId === $this->examples->ltcsBillingStatements[0]->user->userId;
                        },
                    ],
                    'when billingId' => [
                        ['billingId' => $this->examples->ltcsBillings[0]->id],
                        function (LtcsBillingStatement $x): bool {
                            return $x->billingId === $this->examples->ltcsBillings[0]->id;
                        },
                    ],
                    'when bundleId' => [
                        ['bundleId' => $this->examples->ltcsBillingBundles[0]->id],
                        function (LtcsBillingStatement $x): bool {
                            return $x->bundleId === $this->examples->ltcsBillingBundles[0]->id;
                        },
                    ],
                    'when bundleIds' => [
                        [
                            'bundleIds' => [
                                $this->examples->ltcsBillingBundles[0]->id,
                                $this->examples->ltcsBillingBundles[1]->id,
                            ],
                        ],
                        function (LtcsBillingStatement $x): bool {
                            return $x->bundleId === $this->examples->ltcsBillingBundles[0]->id
                                || $x->bundleId === $this->examples->ltcsBillingBundles[1]->id;
                        },
                    ],
                ],
            ],
        );
        $this->should('return FinderResult with sorted list', function (string $key, Closure $sortBy): void {
            $expects = Seq::fromArray($this->examples->ltcsBillingStatements)
                ->sortBy($sortBy);

            $result = $this->finder->find([], ['sortBy' => $key, 'desc' => false, 'all' => true]);

            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals($expects->toArray(), $result->list->toArray());
        }, [
            'examples' => [
                'sortBy date' => [
                    'date',
                    fn (LtcsBillingStatement $x): Carbon => $x->createdAt,
                ],
                'sortBy id' => [
                    'id',
                    fn (LtcsBillingStatement $x): int => $x->id,
                ],
            ],
        ]);
    }
}

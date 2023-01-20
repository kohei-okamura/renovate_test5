<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\UserBilling;

use Closure;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\UserBilling\WithdrawalTransaction;
use Infrastructure\UserBilling\WithdrawalTransactionFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\UserBilling\WithdrawalTransactionFinderEloquentImpl} のテスト.
 */
final class WithdrawalTransactionFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private WithdrawalTransactionFinderEloquentImpl $finder;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->finder = app(WithdrawalTransactionFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of WithdrawalTransaction', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(WithdrawalTransaction::class, $item);
            }
        });
        $this->should('return a FinderResult when itemsPerPage is 1', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->withdrawalTransactions[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->withdrawalTransactions);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->withdrawalTransactions);
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
                $count = count($this->examples->withdrawalTransactions);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->withdrawalTransactions);
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
            'return a FindResult with specified filter params',
            function (array $filter, Closure $f): void {
                $this->assertExists($this->examples->withdrawalTransactions, $this->invert($f));

                $result = $this->finder->find(
                    $filter,
                    [
                        'all' => true,
                        'sortBy' => 'date',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'when deductedOn' => [
                        ['deductedOn' => $this->examples->withdrawalTransactions[0]->deductedOn],
                        fn (WithdrawalTransaction $x): bool => $x->deductedOn->eq($this->examples->withdrawalTransactions[0]->deductedOn),
                    ],
                    'when start' => [
                        ['start' => $this->examples->withdrawalTransactions[0]->createdAt->startOfDay()],
                        fn (WithdrawalTransaction $x): bool => $x->createdAt->gte($this->examples->withdrawalTransactions[0]->createdAt->startOfDay()),
                    ],
                    'when end' => [
                        ['end' => $this->examples->withdrawalTransactions[1]->createdAt->startOfDay()],
                        fn (WithdrawalTransaction $x): bool => $x->createdAt->lte($this->examples->withdrawalTransactions[1]->createdAt->endOfDay()),
                    ],
                ],
            ],
        );
    }
}

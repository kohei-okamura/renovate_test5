<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\LtcsBillingInvoice;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Infrastructure\Billing\LtcsBillingInvoiceFinderEloquentImpl;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\LtcsBillingInvoiceFinderEloquentImpl} のテスト.
 */
final class LtcsBillingInvoiceFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBillingInvoiceFinderEloquentImpl $finder;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->finder = app(LtcsBillingInvoiceFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of LtcsBillingInvoice', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (mixed $x): bool => $x instanceof LtcsBillingInvoice);
        });
        $this->should('return LtcsBillingInvoice Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->ltcsBillingInvoices[0],
                $result->list->head(),
            );
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->ltcsBillingInvoices);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsBillingInvoices);
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
                $count = count($this->examples->ltcsBillingInvoices);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsBillingInvoices);
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
            $result = $this->finder->find(['invalid' => true], ['sortBy' => 'id']);

            $this->assertArrayStrictEquals(
                $this->examples->ltcsBillingInvoices,
                $result->list->toArray()
            );
        });
        $this->should(
            'return a FinderResult of ltcsBillingInvoices with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->ltcsBillingInvoices,
                    $this->invert($f)
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->ltcsBillingInvoices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'when billingId' => [
                        ['billingId' => $this->examples->ltcsBillingInvoices[0]->billingId],
                        function (LtcsBillingInvoice $x): bool {
                            return $x->billingId === $this->examples->ltcsBillingInvoices[0]->billingId;
                        },
                    ],
                    'when bundleId' => [
                        ['bundleId' => $this->examples->ltcsBillingInvoices[1]->bundleId],
                        function (LtcsBillingInvoice $x): bool {
                            return $x->bundleId === $this->examples->ltcsBillingInvoices[1]->bundleId;
                        },
                    ],
                ],
            ],
        );
    }
}

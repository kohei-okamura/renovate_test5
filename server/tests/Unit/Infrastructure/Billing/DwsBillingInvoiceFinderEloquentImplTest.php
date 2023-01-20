<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsBillingInvoice;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\DwsBillingInvoiceFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingInvoiceFinderEloquentImpl} Test.
 */
class DwsBillingInvoiceFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsBillingInvoiceFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsBillingInvoiceFinderEloquentImplTest $self): void {
            $self->finder = app(DwsBillingInvoiceFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsBillingInvoice', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBillingInvoice);
        });
        $this->should('return a example data of DwsBillingInvoice', function (): void {
            $result = $this->finder->find([], ['itemsPerPage' => 1, 'sortBy' => 'id', 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsBillingInvoices[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsBillingInvoices);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingInvoices);
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
                $count = count($this->examples->dwsBillingInvoices);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingInvoices);
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
            'return a FinderResult of dwsBillingInvoices with given parameters',
            function (array $filter, Closure $f, Closure $exist): void {
                $this->assertExists(
                    $this->examples->dwsBillingInvoices,
                    $exist
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'with dwsBillingBundleId' => [
                        ['dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id],
                        fn (DwsBillingInvoice $x): bool => $x->dwsBillingBundleId === $this->examples->dwsBillingBundles[0]->id,
                        fn (DwsBillingInvoice $x): bool => $x->dwsBillingBundleId !== $this->examples->dwsBillingBundles[0]->id,
                    ],
                ],
            ]
        );
        $this->should('return a FinderResult of dwsBillingInvoices with invalid parameters', function (): void {
            $result = $this->finder->find(
                ['invalid' => 1],
                ['all' => true, 'sortBy' => 'id']
            );

            $this->assertNotEmpty($result->list);
            $this->assertSame(count($this->examples->dwsBillingInvoices), $result->pagination->count);
            $this->assertArrayStrictEquals($this->examples->dwsBillingInvoices, $result->list->toArray());
        });
    }
}

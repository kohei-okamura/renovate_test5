<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsBillingBundle;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\DwsBillingBundleFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingBundleFinderEloquentImpl} Test.
 */
class DwsBillingBundleFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsBillingBundleFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsBillingBundleFinderEloquentImplTest $self): void {
            $self->finder = app(DwsBillingBundleFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsBillingBundle', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBillingBundle);
        });
        $this->should('return DwsBillingBundle Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingBundles[0],
                $result->list->head(),
            );
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsBillingBundles);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingBundles);
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
                $count = count($this->examples->dwsBillingBundles);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingBundles);
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
            $expected = Seq::fromArray($this->examples->dwsBillingBundles)->take(10);
            $result = $this->finder->find(['invalid' => true], ['sortBy' => 'id']);

            $this->assertArrayStrictEquals($expected->toArray(), $result->list->toArray());
        });
        $this->should(
            'return a FinderResult of DwsBillingBundles with given parameters',
            function (array $filter, Closure $f, Closure $exists): void {
                $this->assertExists(
                    $this->examples->dwsBillingBundles,
                    $exists
                );
                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->dwsBillingBundles);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'when dwsBillingId' => [
                        ['dwsBillingId' => $this->examples->dwsBillings[0]->id],
                        fn (DwsBillingBundle $x): bool => $x->dwsBillingId === $this->examples->dwsBillings[0]->id,
                        fn (DwsBillingBundle $x): bool => $x->dwsBillingId !== $this->examples->dwsBillings[0]->id,
                    ],
                    'when providedIn' => [
                        ['providedIn' => $this->examples->dwsBillingBundles[0]->providedIn],
                        // Carbon のため厳密な比較はしない
                        fn (DwsBillingBundle $x): bool => $x->providedIn->equalTo($this->examples->dwsBillingBundles[0]->providedIn),
                        fn (DwsBillingBundle $x): bool => $x->providedIn !== $this->examples->dwsBillingBundles[0]->providedIn,
                    ],
                ],
            ],
        );
    }
}

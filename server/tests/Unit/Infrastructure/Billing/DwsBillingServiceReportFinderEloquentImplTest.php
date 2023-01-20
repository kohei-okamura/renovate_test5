<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\DwsBillingServiceReportFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingServiceReportFinderEloquentImpl} Test.
 */
class DwsBillingServiceReportFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsBillingServiceReportFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsBillingServiceReportFinderEloquentImplTest $self): void {
            $self->finder = app(DwsBillingServiceReportFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsBillingCopayCoordination', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBillingServiceReport);
        });
        $this->should('return a example data of DwsBillingCopayCoordination', function (): void {
            $result = $this->finder->find([], ['itemsPerPage' => 1, 'sortBy' => 'id', 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsBillingServiceReports[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsBillingServiceReports);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingServiceReports);
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
                $count = count($this->examples->dwsBillingServiceReports);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingServiceReports);
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
            'return a FinderResult of dwsBillingServiceReports with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->dwsBillingServiceReports,
                    $this->invert($f)
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'with dwsBillingBundleId' => [
                        ['dwsBillingBundleId' => $this->examples->dwsBillingServiceReports[0]->id],
                        fn (DwsBillingServiceReport $x): bool => $x->dwsBillingBundleId === $this->examples->dwsBillingServiceReports[0]->id,
                    ],
                    'with dwsBillingBundleIds' => [
                        ['dwsBillingBundleIds' => [
                            $this->examples->dwsBillingServiceReports[0]->id,
                            $this->examples->dwsBillingServiceReports[1]->id,
                        ]],
                        fn (DwsBillingServiceReport $x): bool => in_array($x->dwsBillingBundleId, [
                            $this->examples->dwsBillingServiceReports[0]->id,
                            $this->examples->dwsBillingServiceReports[1]->id,
                        ], true),
                    ],
                    'with userIds' => [
                        ['userIds' => [
                            $this->examples->users[0]->id,
                            $this->examples->users[1]->id,
                        ]],
                        fn (DwsBillingServiceReport $x): bool => in_array($x->user->userId, [
                            $this->examples->users[0]->id,
                            $this->examples->users[1]->id,
                        ], true),
                    ],
                ],
            ]
        );
        $this->should('return a FinderResult of dwsBillingServiceReports with invalid parameters', function (): void {
            $result = $this->finder->find(
                ['invalid' => 1],
                ['all' => true, 'sortBy' => 'id']
            );

            $this->assertNotEmpty($result->list);
            $this->assertSame(count($this->examples->dwsBillingServiceReports), $result->pagination->count);
            $this->assertArrayStrictEquals($this->examples->dwsBillingServiceReports, $result->list->toArray());
        });
    }
}

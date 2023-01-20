<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\DwsBillingCopayCoordinationFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingCopayCoordinationFinderEloquentImpl} Test.
 */
class DwsBillingCopayCoordinationFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsBillingCopayCoordinationFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsBillingCopayCoordinationFinderEloquentImplTest $self): void {
            $self->finder = app(DwsBillingCopayCoordinationFinderEloquentImpl::class);
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
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBillingCopayCoordination);
        });
        $this->should('return a example data of DwsBillingCopayCoordination', function (): void {
            $result = $this->finder->find([], ['itemsPerPage' => 1, 'sortBy' => 'id', 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsBillingCopayCoordinations[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsBillingCopayCoordinations);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingCopayCoordinations);
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
                $count = count($this->examples->dwsBillingCopayCoordinations);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingCopayCoordinations);
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
            'return a FinderResult of dwsBillingCopayCoordinations with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->dwsBillingCopayCoordinations,
                    $this->invert($f)
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'with dwsBillingId' => [
                        ['dwsBillingId' => $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingId],
                        fn (DwsBillingCopayCoordination $x): bool => $x->dwsBillingId === $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingId,
                    ],
                    'with dwsBillingBundleId' => [
                        ['dwsBillingBundleId' => $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId],
                        fn (DwsBillingCopayCoordination $x): bool => $x->dwsBillingBundleId === $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId,
                    ],
                    'with dwsBillingBundleIds' => [
                        ['dwsBillingBundleIds' => [
                            $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId,
                            $this->examples->dwsBillingCopayCoordinations[1]->dwsBillingBundleId,
                        ]],
                        fn (DwsBillingCopayCoordination $x): bool => in_array($x->dwsBillingBundleId, [
                            $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId,
                            $this->examples->dwsBillingCopayCoordinations[1]->dwsBillingBundleId,
                        ], true),
                    ],
                    'with userIds' => [
                        ['userIds' => [
                            $this->examples->users[4]->id,
                            $this->examples->users[5]->id,
                        ]],
                        fn (DwsBillingCopayCoordination $x): bool => in_array($x->user->userId, [
                            $this->examples->users[4]->id,
                            $this->examples->users[5]->id,
                        ], true),
                    ],
                ],
            ]
        );
        $this->should('return a FinderResult of dwsBillingCopayCoordinations with invalid parameters', function (): void {
            $result = $this->finder->find(
                ['invalid' => 1],
                ['all' => true, 'sortBy' => 'id']
            );

            $this->assertNotEmpty($result->list);
            $this->assertSame(count($this->examples->dwsBillingCopayCoordinations), $result->pagination->count);
            $this->assertArrayStrictEquals($this->examples->dwsBillingCopayCoordinations, $result->list->toArray());
        });
    }
}

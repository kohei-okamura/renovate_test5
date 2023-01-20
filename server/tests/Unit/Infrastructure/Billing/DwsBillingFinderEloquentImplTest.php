<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\DwsBillingFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingFinderEloquentImpl} Test.
 */
class DwsBillingFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsBillingFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsBillingFinderEloquentImplTest $self): void {
            $self->finder = app(DwsBillingFinderEloquentImpl::class);
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
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBilling);
        });
        $this->should('return DwsBilling Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillings[0],
                $result->list->head(),
            );
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsBillings);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillings);
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
                $count = count($this->examples->dwsBillings);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillings);
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
                $this->examples->dwsBillings,
                $result->list->toArray()
            );
        });
        $this->should(
            'return a FinderResult of DwsBillings with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->dwsBillings,
                    $this->invert($f)
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->dwsBillings);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'when transactedInBefore' => [
                        ['transactedInBefore' => Carbon::parse('2021-02')],
                        fn (DwsBilling $x): bool => $x->transactedIn <= Carbon::parse('2021-02-01'),
                    ],
                    'when officeId' => [
                        ['officeId' => $this->examples->offices[1]->id],
                        fn (DwsBilling $x): bool => $x->office->officeId === $this->examples->offices[1]->id,
                    ],
                    'when officeIds' => [
                        ['officeIds' => [$this->examples->offices[1]->id]],
                        fn (DwsBilling $x): bool => $x->office->officeId === $this->examples->offices[1]->id,
                    ],
                    'when organizationId' => [
                        ['organizationId' => $this->examples->organizations[0]->id],
                        fn (DwsBilling $x): bool => $x->organizationId === $this->examples->organizations[0]->id,
                    ],
                    'when transactedInAfter' => [
                        ['transactedInAfter' => Carbon::parse('2020-12')],
                        fn (DwsBilling $x): bool => $x->transactedIn >= Carbon::parse('2020-12-01'),
                    ],
                    'when status' => [
                        ['status' => DwsBillingStatus::fixed()],
                        fn (DwsBilling $x): bool => $x->status === DwsBillingStatus::fixed(),
                    ],
                    'when statuses that is a single element' => [
                        ['statuses' => DwsBillingStatus::fixed()],
                        fn (DwsBilling $x): bool => $x->status === DwsBillingStatus::fixed(),
                    ],
                    'when statuses that is array' => [
                        ['statuses' => [DwsBillingStatus::checking(), DwsBillingStatus::fixed()]],
                        fn (DwsBilling $x): bool => in_array($x->status, [DwsBillingStatus::checking(), DwsBillingStatus::fixed()], true),
                    ],
                    'when transactedIn' => [
                        ['transactedIn' => Carbon::parse('2021-01-01')],
                        fn (DwsBilling $x): bool => $x->transactedIn->eq(Carbon::parse('2021-01-01')),
                    ],
                ],
            ],
        );
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\LtcsBillingFinderEloquentImpl;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\LtcsBillingFinderEloquentImpl} のテスト.
 */
final class LtcsBillingFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBillingFinderEloquentImpl $finder;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->finder = app(LtcsBillingFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of LtcsBillingBundle', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof LtcsBilling);
        });
        $this->should('return LtcsBilling Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->ltcsBillings[0],
                $result->list->head(),
            );
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->ltcsBillings);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsBillings);
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
                $count = count($this->examples->ltcsBillings);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ltcsBillings);
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
                $this->examples->ltcsBillings,
                $result->list->toArray()
            );
        });
        $this->should(
            'return a FinderResult of LtcsBillings with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->ltcsBillings,
                    $this->invert($f)
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->ltcsBillings);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'when transactedInBefore' => [
                        ['transactedInBefore' => Carbon::parse('2021-02')],
                        fn (LtcsBilling $x): bool => $x->transactedIn <= Carbon::parse('2021-02-01'),
                    ],
                    'when officeId' => [
                        ['officeId' => $this->examples->offices[0]->id],
                        fn (LtcsBilling $x): bool => $x->office->officeId === $this->examples->offices[0]->id,
                    ],
                    'when officeIds' => [
                        ['officeIds' => [$this->examples->offices[0]->id]],
                        fn (LtcsBilling $x): bool => $x->office->officeId === $this->examples->offices[0]->id,
                    ],
                    'when organizationId' => [
                        ['organizationId' => $this->examples->organizations[0]->id],
                        fn (LtcsBilling $x): bool => $x->organizationId === $this->examples->organizations[0]->id,
                    ],
                    'when transactedInAfter' => [
                        ['transactedInAfter' => Carbon::parse('2020-12')],
                        fn (LtcsBilling $x): bool => $x->transactedIn >= Carbon::parse('2020-12-01'),
                    ],
                    'when status' => [
                        ['status' => LtcsBillingStatus::fixed()],
                        fn (LtcsBilling $x): bool => $x->status === LtcsBillingStatus::fixed(),
                    ],
                    'when statuses that is a single element' => [
                        ['statuses' => LtcsBillingStatus::fixed()],
                        fn (LtcsBilling $x): bool => $x->status === LtcsBillingStatus::fixed(),
                    ],
                    'when statuses that is array' => [
                        ['statuses' => [LtcsBillingStatus::checking(), LtcsBillingStatus::fixed()]],
                        fn (LtcsBilling $x): bool => in_array($x->status, [LtcsBillingStatus::checking(), LtcsBillingStatus::fixed()], true),
                    ],
                    'when transactedIn' => [
                        ['transactedIn' => $this->examples->ltcsBillings[0]->transactedIn],
                        fn (LtcsBilling $x): bool => $x->transactedIn->eq($this->examples->ltcsBillings[0]->transactedIn),
                    ],
                ],
            ],
        );
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ProvisionReport;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\User\User;
use Infrastructure\ProvisionReport\DwsProvisionReportFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ProvisionReport\DwsProvisionReportFinderEloquentImpl} のテスト.
 */
final class DwsProvisionReportFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsProvisionReportFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->finder = app(DwsProvisionReportFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsProvisionReport', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(DwsProvisionReport::class, $item);
            }
        });
        $this->should('return a FinderResult when itemsPerPage is 1', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsProvisionReports[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->dwsProvisionReports);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsProvisionReports);
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
                $count = count($this->examples->dwsProvisionReports);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsProvisionReports);
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
            $result = $this->finder->find(['invalid' => true], ['all' => true, 'sortBy' => 'id']);

            $this->assertArrayStrictEquals(
                $this->examples->dwsProvisionReports,
                $result->list->toArray()
            );
        });
        $this->should(
            'return a FindResult with specified filter params',
            function (array $filter, Closure $f): void {
                $this->assertExists($this->examples->dwsProvisionReports, $this->invert($f));
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
                    'when fixedAt' => [
                        [
                            'fixedAt' => CarbonRange::create([
                                'start' => Carbon::create(2021, 1, 28, 15, 0, 0),
                                'end' => Carbon::create(2021, 1, 29, 12, 34, 56),
                            ]),
                        ],
                        function (DwsProvisionReport $x): bool {
                            $range = CarbonRange::create([
                                'start' => Carbon::create(2021, 1, 28, 15, 0, 0),
                                'end' => Carbon::create(2021, 1, 29, 12, 34, 56),
                            ]);
                            return $range->contains($x->fixedAt);
                        },
                    ],
                    'when officeId' => [
                        ['officeId' => $this->examples->offices[0]->id],
                        fn (DwsProvisionReport $x): bool => $x->officeId === $this->examples->offices[0]->id,
                    ],
                    'when officeIds' => [
                        ['officeIds' => [$this->examples->offices[0]->id, $this->examples->offices[1]->id]],
                        fn (DwsProvisionReport $x): bool => in_array(
                            $x->officeId,
                            [$this->examples->offices[0]->id, $this->examples->offices[1]->id],
                            true
                        ),
                    ],
                    'when providedIn' => [
                        ['providedIn' => $this->examples->dwsProvisionReports[0]->providedIn],
                        fn (
                            DwsProvisionReport $x
                        ): bool => $x->providedIn->isSameMonth($this->examples->dwsProvisionReports[0]->providedIn),
                    ],
                    'when status' => [
                        ['status' => $this->examples->dwsProvisionReports[0]->status],
                        fn (DwsProvisionReport $x): bool => in_array(
                            $x->status,
                            [$this->examples->dwsProvisionReports[0]->status],
                            true
                        ),
                    ],
                    'when userId' => [
                        ['userId' => $this->examples->users[0]->id],
                        fn (DwsProvisionReport $x): bool => $x->userId === $this->examples->users[0]->id,
                    ],
                    'when userIds' => [
                        ['userIds' => [$this->examples->users[0]->id]],
                        fn (DwsProvisionReport $x): bool => $x->userId === $this->examples->users[0]->id,
                    ],
                    'when organizationId' => [
                        ['organizationId' => $this->examples->users[0]->organizationId],
                        function (DwsProvisionReport $x) {
                            $organizationId = $this->examples->users[0]->organizationId;
                            $user = Seq::fromArray($this->examples->users)->exists(function (User $user) use ($x, $organizationId) {
                                return $user->organizationId === $organizationId && $user->id === $x->userId;
                            });
                            $office = Seq::fromArray($this->examples->offices)->exists(function (Office $office) use ($x, $organizationId) {
                                return $office->organizationId === $organizationId && $office->id === $x->officeId;
                            });
                            return $user && $office;
                        },
                    ],
                ],
            ],
        );
    }
}

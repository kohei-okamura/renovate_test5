<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shift;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Model;
use Domain\Shift\Assignee;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Illuminate\Support\LazyCollection;
use Infrastructure\Shift\ShiftFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * ShiftFinderEloquentImpl のテスト.
 */
class ShiftFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private ShiftFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (ShiftFinderEloquentImplTest $self): void {
            $self->finder = app(ShiftFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of Shift', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Shift::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->shifts);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->shifts);
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
                $count = count($this->examples->shifts);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->shifts);
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
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $this->finder->find(
                            [],
                            ['all' => true]
                        );
                    }
                );
            }
        );
        $this->should(
            'sort Shifts using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->shifts)
                    ->filter(fn (Shift $shift) => $shift->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy(fn (Shift $shift) => $shift->schedule->start->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->find($filterParams, $paginationParams)->list as $index => $shift) {
                    // optionsの中身は順不同のため、個別に検証
                    $this->assertModelStrictEquals(
                        $expected[$index]->copy(['options' => []] + $expected[$index]->toAssoc()),
                        $shift->copy(['options' => []] + $shift->toAssoc())
                    );
                    $this->assertCount(count($expected[$index]->options), $shift->options);
                    foreach ($expected[$index]->options as $expectedOption) {
                        $this->assertExists(
                            $shift->options,
                            fn (ServiceOption $actualOption): bool => $expectedOption->value() === $actualOption->value()
                        );
                    }
                }
            }
        );
        $this->should('return a FinderResult of shifts with given `userId`', function (): void {
            $result = $this->finder->find(['userId' => $this->examples->users[0]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->userId === $this->examples->users[0]->id
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => $x->userId !== $this->examples->users[0]->id
            );
        });
        $this->should('return a FinderResult of shifts with given `assigneeId`', function (): void {
            $result = $this->finder->find(['assigneeId' => $this->examples->staffs[10]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                function (Shift $x): bool {
                    foreach ($x->assignees as $assignee) {
                        if ($assignee->staffId === $this->examples->staffs[10]->id) {
                            return true;
                        }
                    }
                    return false;
                }
            );
            $this->assertExists(
                $this->examples->shifts,
                function (Shift $x): bool {
                    foreach ($x->assignees as $assignee) {
                        if ($assignee->staffId === $this->examples->staffs[10]->id) {
                            return false;
                        }
                    }
                    return true;
                }
            );
        });
        $this->should('return a FinderResult of shifts with given `assignerId`', function (): void {
            $result = $this->finder->find(['assignerId' => $this->examples->staffs[10]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->assignerId === $this->examples->staffs[10]->id
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => $x->assignerId !== $this->examples->staffs[10]->id
            );
        });
        $this->should('return a FinderResult of shifts with given `officeId`', function (): void {
            $result = $this->finder->find(['officeId' => $this->examples->offices[0]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->officeId === $this->examples->offices[0]->id
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => $x->officeId !== $this->examples->offices[0]->id
            );
        });
        $this->should('return a FinderResult of shifts with given `officeIds`', function (): void {
            $officeIds = [$this->examples->offices[0]->id, $this->examples->offices[1]->id];
            $result = $this->finder->find(
                ['officeIds' => $officeIds],
                ['sortBy' => 'date']
            );

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => in_array($x->officeId, $officeIds, true)
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => !in_array($x->officeId, $officeIds, true)
            );
        });
        $this->should('return a FinderResult of shifts with given `task`', function (): void {
            $result = $this->finder->find(['task' => Task::assessment()], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->task === Task::assessment()
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => $x->task !== Task::assessment()
            );
        });
        $this->should(
            'return a FinderResult of shifts with given params',
            function (array $condition, Closure $assert, Closure $exist): void {
                $result = $this->finder->find($condition, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->shifts);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    $assert,
                );
                $this->assertExists(
                    $this->examples->shifts,
                    $exist
                );
            },
            [
                'examples' => [
                    'isConfirmed is false' => [
                        ['isConfirmed' => false],
                        fn (Shift $x): bool => $x->isConfirmed === false,
                        fn (Shift $x): bool => $x->isConfirmed !== false,
                    ],
                    'isConfirmed is true' => [
                        ['isConfirmed' => true],
                        fn (Shift $x): bool => $x->isConfirmed === true,
                        fn (Shift $x): bool => $x->isConfirmed !== true,
                    ],
                ],
            ]
        );
        $this->should('return a FinderResult of shifts with the start date on or after given `start`', function (): void {
            $start = Carbon::parse('2040-04-10');
            $result = $this->finder->find(['scheduleDateAfter' => $start], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->schedule->date->isSameAs($start) || $x->schedule->date->isAfter($start)
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => $x->schedule->date->isBefore($start)
            );
        });
        $this->should('return a FinderResult of shifts with the end date on or after given `end`', function (): void {
            $end = Carbon::parse('2040-04-10');
            $result = $this->finder->find(['scheduleDateBefore' => $end], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->schedule->date->isSameAs($end) || $x->schedule->date->isBefore($end)
            );
            $this->assertExists(
                $this->examples->shifts,
                fn (Shift $x): bool => $x->schedule->date->isAfter($end)
            );
        });
        $this->should('return a FindResult of shifts with specified `assigneeIds`', function (): void {
            $assigneeIds = [
                $this->examples->shifts[1]->assignees[0]->staffId,
                $this->examples->shifts[2]->assignees[0]->staffId,
            ];

            $result = $this->finder->find(compact('assigneeIds'), ['sortBy' => 'id']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                function (Shift $x) use ($assigneeIds): bool {
                    $xs = Seq::fromArray($x->assignees)
                        ->map(fn (Assignee $assignee): int => $assignee->staffId)
                        ->toArray();
                    return in_array($assigneeIds[0], $xs, true) || in_array($assigneeIds[1], $xs, true);
                }
            );
        });
        $this->should('return a FinderResult of shifts with the `date`', function (): void {
            $date = Carbon::parse('2040-01-01');
            $result = $this->finder->find(['date' => $date], ['sortBy' => 'id']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->schedule->date->isSameAs($date)
            );
        });
        $this->should('return a FinderResult of shifts with the `startTime`', function (): void {
            $startTime = Carbon::parse('2040-11-12 10:00');
            $result = $this->finder->find(['startTime' => $startTime], ['sortBy' => 'id']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->schedule->start->isSameAs($startTime)
            );
        });
        $this->should('return a FinderResult of shifts with given `endDate`', function (): void {
            $result = $this->finder->find(['endDate' => Carbon::parse('2030-11-12')], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->schedule->end->toDateString() === '2030-11-12'
            );
        });
        $this->should('return a FinderResult of shifts with given `scheduleStart`', function (): void {
            $start = Carbon::parse('2030-11-12 13:00');
            $end = Carbon::parse('2030-11-12 14:00');
            $result = $this->finder->find(['scheduleStart' => CarbonRange::create([
                'start' => $start,
                'end' => $end,
            ])], [
                'sortBy' => 'date',
            ]);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->schedule->start >= $start && $x->schedule->start <= $end
            );
        });
        $this->should('return a FinderResult of shifts with given `notificationEnabled`', function (): void {
            $result = $this->finder->find(['notificationEnabled' => true], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => Seq::fromArray($x->options)
                    ->find(fn (ServiceOption $x): bool => $x === ServiceOption::notificationEnabled())
                    ->nonEmpty(),
            );
        });
        $this->should('return a FinderResult of shifts with the `excludeOption`', function (): void {
            $result = $this->finder->find(['excludeOption' => Seq::fromArray([ServiceOption::oneOff()])], ['sortBy' => 'id']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => !in_array(ServiceOption::oneOff(), $x->options, true)
            );
        });
        $this->should('return a FinderResult of shifts with given `isCanceled`', function (): void {
            $result = $this->finder->find(['isCanceled' => true], ['sortBy' => 'date']);
            $this->assertNotEmpty($this->examples->shifts);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Shift $x): bool => $x->isCanceled === true
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_cursor(): void
    {
        $this->should('return a LazyCollection of Shift', function (): void {
            $result = $this->finder->cursor([], ['sortBy' => 'date']);

            $this->assertInstanceOf(LazyCollection::class, $result);
            $this->assertForAll($result, fn (Model $x): bool => $x instanceof Shift);
        });
        $this->should(
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $this->finder->cursor(
                            [],
                            []
                        );
                    }
                );
            }
        );
        $this->should(
            'sort Shifts using given param `sortBy` and `desc`',
            function (string $sortBy, Closure $orderExample): void {
                $expected = Seq::fromArray($this->examples->shifts)
                    ->filter(fn (Shift $shift) => $shift->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy($orderExample)
                    ->reverse()
                    ->toArray();
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->cursor($filterParams, ['sortBy' => $sortBy, 'desc' => true]) as $index => $shift) {
                    // optionsの中身は順不同のため、個別に検証
                    $this->assertModelStrictEquals(
                        $expected[$index]->copy(['options' => []] + $expected[$index]->toAssoc()),
                        $shift->copy(['options' => []] + $shift->toAssoc())
                    );
                    $this->assertCount(count($expected[$index]->options), $shift->options);
                    foreach ($expected[$index]->options as $expectedOption) {
                        $this->assertExists(
                            $shift->options,
                            fn (ServiceOption $actualOption): bool => $expectedOption->value() === $actualOption->value()
                        );
                    }
                }
            },
            [
                'examples' => [
                    'sort By date' => [
                        'date',
                        fn (Shift $shift) => $shift->schedule->start->unix(),
                    ],
                ],
            ]
        );
        $this->should('sort Shifts using `sortBy date` and `desc`', function (): void {
            $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
            $pastId = 0;
            foreach ($this->finder->cursor($filterParams, ['sortBy' => 'userId', 'desc' => true]) as $shift) {
                assert($shift instanceof Shift);
                // 同一 userId のものが複数あるので、userId だけで比較する
                if ($pastId !== 0) {
                    $this->assertLessThanOrEqual($pastId, $shift->userId);
                }
                $pastId = $shift->userId;
            }
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shift;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Shift\Attendance;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Infrastructure\Shift\AttendanceFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * AttendanceFinderEloquentImpl のテスト.
 */
final class AttendanceFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private AttendanceFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (AttendanceFinderEloquentImplTest $self): void {
            $self->finder = app(AttendanceFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of Attendance', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Attendance::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->attendances);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($result);
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
                $count = count($this->examples->attendances);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->attendances);
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
            'sort Attendances using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->attendances)
                    ->filter(fn (Attendance $x): bool => $x->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy(fn (Attendance $x): int => $x->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->find($filterParams, $paginationParams)->list as $index => $attendance) {
                    assert($attendance instanceof Attendance);
                    // optionsの中身は順不同のため、個別に検証
                    $this->assertModelStrictEquals(
                        $expected[$index]->copy(['options' => []] + $expected[$index]->toAssoc()),
                        $attendance->copy(['options' => []] + $attendance->toAssoc())
                    );
                    $this->assertCount(count($expected[$index]->options), $attendance->options);
                    foreach ($expected[$index]->options as $expectedOption) {
                        $this->assertExists(
                            $attendance->options,
                            fn (
                                ServiceOption $actualOption
                            ): bool => $expectedOption->value() === $actualOption->value()
                        );
                    }
                }
            }
        );
        $this->should('return a FinderResult of attendances with given `userId`', function (): void {
            $result = $this->finder->find(['userId' => $this->examples->users[0]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->attendances);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Attendance $x): bool => $x->userId === $this->examples->users[0]->id
            );
            $this->assertExists(
                $this->examples->attendances,
                fn (Attendance $x): bool => $x->userId !== $this->examples->users[0]->id
            );
        });
        $this->should('return a FinderResult of attendances with given `assigneeId`', function (): void {
            $result = $this->finder->find(['assigneeId' => $this->examples->staffs[10]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->attendances);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                function (Attendance $x): bool {
                    foreach ($x->assignees as $assignee) {
                        if ($assignee->staffId === $this->examples->staffs[10]->id) {
                            return true;
                        }
                    }
                    return false;
                }
            );
            $this->assertExists(
                $this->examples->attendances,
                function (Attendance $x): bool {
                    foreach ($x->assignees as $assignee) {
                        if ($assignee->staffId === $this->examples->staffs[10]->id) {
                            return false;
                        }
                    }
                    return true;
                }
            );
        });
        $this->should('return a FinderResult of attendances with given `assignerId`', function (): void {
            $result = $this->finder->find(['assignerId' => $this->examples->staffs[10]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->attendances);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Attendance $x): bool => $x->assignerId === $this->examples->staffs[10]->id
            );
            $this->assertExists(
                $this->examples->attendances,
                fn (Attendance $x): bool => $x->assignerId !== $this->examples->staffs[10]->id
            );
        });
        $this->should('return a FinderResult of attendances with given `officeId`', function (): void {
            $result = $this->finder->find(['officeId' => $this->examples->offices[0]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->attendances);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Attendance $x): bool => $x->officeId === $this->examples->offices[0]->id
            );
            $this->assertExists(
                $this->examples->attendances,
                fn (Attendance $x): bool => $x->officeId !== $this->examples->offices[0]->id
            );
        });
        $this->should('return a FinderResult of attendances with given `officeIds`', function (): void {
            $officeIds = [$this->examples->offices[0]->id, $this->examples->offices[1]->id];
            $result = $this->finder->find(
                ['officeIds' => $officeIds],
                ['sortBy' => 'date']
            );

            $this->assertNotEmpty($this->examples->attendances);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Attendance $x): bool => in_array($x->officeId, $officeIds, true)
            );
            $this->assertExists(
                $this->examples->attendances,
                fn (Attendance $x): bool => !in_array($x->officeId, $officeIds, true)
            );
        });
        $this->should('return a FinderResult of attendances with given `task`', function (): void {
            $result = $this->finder->find(['task' => Task::assessment()], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->attendances);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Attendance $x): bool => $x->task === Task::assessment()
            );
            $this->assertExists(
                $this->examples->attendances,
                fn (Attendance $x): bool => $x->task !== Task::assessment()
            );
        });
        $this->should('return a FinderResult of attendances with given array of `tasks`', function (): void {
            $tasks = [Task::commAccompany(), Task::ltcsPhysicalCareAndHousework()];
            $result = $this->finder->find(
                compact('tasks'),
                ['sortBy' => 'date']
            );

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Attendance $x): bool => in_array($x->task, $tasks, true)
            );
            $this->assertExists(
                $this->examples->attendances,
                fn (Attendance $x): bool => !in_array($x->task, $tasks, true)
            );
        });
        $this->should(
            'return a FinderResult of attendances with given params',
            function (array $condition, Closure $assert, Closure $exist): void {
                $result = $this->finder->find($condition, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->attendances);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    $assert,
                );
                $this->assertExists(
                    $this->examples->attendances,
                    $exist
                );
            },
            [
                'examples' => [
                    'isConfirmed is false' => [
                        ['isConfirmed' => false],
                        fn (Attendance $x): bool => $x->isConfirmed === false,
                        fn (Attendance $x): bool => $x->isConfirmed !== false,
                    ],
                    'isConfirmed is true' => [
                        ['isConfirmed' => true],
                        fn (Attendance $x): bool => $x->isConfirmed === true,
                        fn (Attendance $x): bool => $x->isConfirmed !== true,
                    ],
                ],
            ]
        );
        $this->should(
            'return a FinderResult of attendances with the start date on or after given `start`',
            function (): void {
                $start = Carbon::parse('2040-04-10');
                $result = $this->finder->find(['scheduleDateAfter' => $start], ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->attendances);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Attendance $x): bool => $x->schedule->date >= $start
                );
                $this->assertExists(
                    $this->examples->attendances,
                    fn (Attendance $x): bool => $x->schedule->date < $start
                );
            }
        );
        $this->should(
            'return a FinderResult of attendances with the end date on or after given `end`',
            function (): void {
                $end = Carbon::parse('2040-04-10');
                $result = $this->finder->find(['scheduleDateBefore' => $end], ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->attendances);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Attendance $x): bool => $x->schedule->date <= $end
                );
                $this->assertExists(
                    $this->examples->attendances,
                    fn (Attendance $x): bool => $x->schedule->date > $end
                );
            }
        );
        $this->should('sort Attendances using `sortBy date` and `desc`', function (): void {
            $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
            $result = $this->finder->find($filterParams, ['sortBy' => 'userId', 'desc' => true]);

            $this->assertNotEmpty($result->list);
            $pastId = 0;
            foreach ($result->list as $entity) {
                assert($entity instanceof Attendance);
                // 同一 userId のものが複数あるので、userId だけで比較する
                if ($pastId !== 0) {
                    $this->assertLessThanOrEqual($pastId, $entity->userId);
                }
                $pastId = $entity->userId;
            }
        });
    }
}

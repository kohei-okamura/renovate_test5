<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Attendance;

use ApiTester;
use Domain\Common\Carbon;
use Domain\Shift\Attendance;
use Domain\Shift\AttendanceFinder;
use Domain\Shift\AttendanceRepository;
use Domain\Shift\Shift;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * BulkCreateAttendanceCommand のテスト
 */
class BulkCreateAttendanceCommandCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * Artisan Command テスト.
     *
     * @param ApiTester $I
     */
    public function succeedArtisanCommand(ApiTester $I)
    {
        $I->wantTo('succeed artisan command');

        $organization = $this->examples->organizations[0];

        // 検証簡略化のためデータを削除する。
        /** @var \Domain\Shift\AttendanceFinder $finder */
        $finder = app(AttendanceFinder::class);
        $ids = $finder->find(
            ['organizationId' => $organization->id, 'isConfirmed' => false],
            ['all' => true, 'sortBy' => 'id']
        )
            ->list
            ->map(fn (Attendance $x): int => $x->id);
        /** @var \Domain\Shift\AttendanceRepository $repository */
        $repository = app(AttendanceRepository::class);
        $repository->removeById(...$ids);

        $targetDate = Carbon::parse('2040-11-12')->toDateString();
        $expects = Seq::fromArray($this->examples->shifts)
            ->filter(function (Shift $x) use ($organization, $targetDate): bool {
                return $x->organizationId === $organization->id
                    && $x->isConfirmed === true
                    && $x->schedule->end >= Carbon::parse($targetDate)->startOfDay()
                    && $x->schedule->end <= Carbon::parse($targetDate)->endOfDay();
            })
            ->sortBy(fn (Shift $x): int => $x->id);

        $attendance = $I->callArtisanCommand('attendance:create-from-shifts', [
            '--targetDate' => $targetDate,
            '--organization' => $organization->code,
        ]);

        $actuals = $finder->find(
            ['organizationId' => $organization->id, 'isConfirmed' => false],
            ['all' => true, 'sortBy' => 'id']
        )
            ->list
            ->toArray();

        assertSame(self::COMMAND_SUCCESS, $attendance);
        assertCount(
            $expects->count(),
            $actuals
        );
        array_map(
            function (Shift $x, int $index) use ($actuals) {
                /** @var \Domain\Shift\Attendance $attendance */
                $attendance = $actuals[$index];
                assertSame($x->userId, $attendance->userId);
                assertSame($x->assignerId, $attendance->assignerId);
                assertSame($x->task, $attendance->task);
                assertEquals($x->serviceCode, $attendance->serviceCode);
                assertEquals($x->assignees, $attendance->assignees);
                assertEquals($x->schedule, $attendance->schedule);
                assertEquals($x->durations, $attendance->durations);
                assertSame($x->options, $attendance->options);
                assertSame(false, $attendance->isConfirmed);
            },
            $expects->toArray(),
            array_keys($expects->toArray())
        );
        $I->seeLogCount(2);
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Calling;

use ApiTester;
use Domain\Calling\CallingFinder;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Schedule;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\ShiftFinder;
use Domain\Shift\ShiftRepository;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * CreateCallingCommand のテスト.
 */
class CreateCallingCommandCest extends Test
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

        // 検証簡略化のためデータをキャンセルにする。
        /** @var \Domain\Shift\ShiftFinder $finder */
        $finder = app(ShiftFinder::class);
        $ids = $finder->find([], ['all' => true, 'sortBy' => 'id'])
            ->list
            ->map(fn (Shift $x): int => $x->id);
        /** @var \Domain\Shift\ShiftRepository $repository */
        $repository = app(ShiftRepository::class);
        $repository->lookup(...$ids)
            ->each(function (Shift $x) use ($repository): void {
                $repository->store($x->copy(['isCanceled' => true]));
            });

        // データ登録
        $target = Carbon::now()->addMinutes(120);
        $shift = $this->examples->shifts[4]->copy([
            'id' => null,
            'schedule' => Schedule::create([
                'start' => $target,
                'end' => $target->addMinutes(60), // duration が60なので
                'date' => $target->startOfDay(),
            ]),
            'options' => [ServiceOption::notificationEnabled()],
            'isCanceled' => false,
            'isConfirmed' => true,
        ]);
        $shiftId = $repository->store($shift)->id;

        // コマンド実行
        $result = $I->callArtisanCommand('calling:create', [
            '--batch' => true,
        ]);

        $I->seeLogCount(0);
        assertSame(self::COMMAND_SUCCESS, $result);

        // expired から狙いの出勤確認が入っていることを検証
        /** @var \Domain\Calling\CallingFinder $callingFinder */
        $callingFinder = app(CallingFinder::class);
        $actuals = $callingFinder->find([
            'expiredRange' => CarbonRange::create([
                'start' => $target,
                'end' => $target,
            ]),
        ], ['all' => true, 'sortBy' => 'id'])->list;

        assertCount(1, $actuals);
        /** @var \Domain\Calling\Calling $calling */
        $calling = $actuals->head();
        assertSame($shift->assignees[0]->staffId, $calling->staffId);
        assertSame([$shiftId], $calling->shiftIds);
        assertEquals($target, $calling->expiredAt);
    }
}

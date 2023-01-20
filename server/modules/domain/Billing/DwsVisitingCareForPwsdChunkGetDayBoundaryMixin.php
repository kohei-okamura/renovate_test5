<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護） `split` 実装.
 *
 * @mixin \Domain\Billing\DwsVisitingCareForPwsdChunk
 */
trait DwsVisitingCareForPwsdChunkGetDayBoundaryMixin
{
    /**
     * 1日の区切り位置を取得する.
     *
     * @param \Domain\Common\Carbon $start
     * @return \Carbon\CarbonImmutable|\Domain\Common\Carbon
     */
    public static function getDayBoundary(Carbon $start): Carbon
    {
        // Carbon の `endOfDay` は 23:59:59 を返すため「翌日」の `startOfDay` を取得する.
        $endOfDay = $start->addDay()->startOfDay();
        $duration = $start->diffInMinutes($endOfDay);

        // 1日目の時間数が1時間（60分）に満たない場合：最初の1時間を1日目とする
        if ($duration < self::MIN_DURATION_MINUTES_OF_FIRST_HOUR) {
            return $start->addMinutes(self::MIN_DURATION_MINUTES_OF_FIRST_HOUR);
        }

        // 1日目の時間数が30分単位となるようにする
        $fraction = $duration % self::MIN_DURATION_MINUTES;
        return $fraction === 0
            ? $endOfDay
            : $endOfDay->addMinutes(self::MIN_DURATION_MINUTES - $fraction);
    }
}

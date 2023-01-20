<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Shift\Activity;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 入力値の「勤務内容」の合計が「勤務時間」と等しいことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DurationsEqualToScheduleRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDurationsEqualToSchedule(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'durations_equal_to_schedule');
        $start = Arr::get($this->data, $parameters[0]);
        $end = Arr::get($this->data, $parameters[1]);
        if (!is_array($value)) {
            // 入力値が配列でない場合はこのバリデーションではエラーとしない
            return true;
        } elseif ($start === null || $end === null) {
            // 勤務時間の開始と終了のどちらかが未入力の場合はこのバリデーションではエラーとしない
            return true;
        } elseif (
            !$this->validateDateFormat('start', $start, ['H:i'])
            || !$this->validateDateFormat('end', $end, ['H:i'])
        ) {
            // 勤務時間の開始と終了のどちらかが不正なフォーマットの場合はこのバリデーションではエラーとしない
            return true;
        } elseif (
        Seq::fromArray($value)->exists(fn (array $x): bool => !$this->validateActivity($attribute, $x['activity'], []))
        ) {
            // 不正な勤務内容を含む場合はこのバリデーションではエラーとしない
            return true;
        } elseif (
        Seq::fromArray($value)->exists(fn (array $x): bool => !is_int($x['duration']))
        ) {
            // 整数でない所要時間を含む場合はこのバリデーションではエラーとしない
            return true;
        } else {
            $totalDuration = Seq::fromArray($value)
                ->filter(fn (array $x): bool => $x['activity'] !== Activity::dwsOutingSupportForPwsd()->value())
                ->map(fn (array $x): int => $x['duration'])
                ->sum();
            $startDateTime = Carbon::createFromFormat('H:i', $start);
            $endDateTime = Carbon::createFromFormat('H:i', $end);
            return $totalDuration === $endDateTime->diffInMinutes($startDateTime);
        }
    }
}

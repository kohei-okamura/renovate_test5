<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Shift\Activity;
use Domain\Shift\Task;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 入力値の「勤務区分」と「勤務内容」の整合性が取れていることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait HaveIntegrityOfRule
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
    protected function validateHaveIntegrityOf(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'have_integrity_of');
        $taskValue = (int)Arr::get($this->data, $parameters[0]);

        // 勤務区分が不正の場合はこのバリデーションではエラーとしない
        if (!Task::isValid($taskValue)) {
            return true;
        }

        // 勤務内容が不正の場合はこのバリデーションではエラーとしない
        assert(is_array($value));
        $durations = Seq::fromArray($value);
        if (!$durations->forAll(fn (array $duration): bool => Activity::isValid($duration['activity']))) {
            return true;
        }

        // 入力値 durations から休憩の値を除いた勤務内容が勤務区分を変換した勤務内容に一致するかチェック
        $task = Task::from($taskValue);
        $activities = $durations->map(fn (array $duration): Activity => Activity::from($duration['activity']));
        $extraActivities = $task->equals(Task::dwsVisitingCareForPwsd())
            ? [Activity::resting(), Activity::dwsOutingSupportForPwsd()]
            : [Activity::resting()];
        $expectedActivities = $task->toActivitiesSeq()->append($extraActivities);
        $unexpectedActivities = $activities->filterNot(fn (Activity $x): bool => $expectedActivities->contains($x));
        return $unexpectedActivities->isEmpty();
    }
}

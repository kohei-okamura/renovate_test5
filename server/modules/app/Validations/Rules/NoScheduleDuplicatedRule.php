<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 同一時間帯の予定または実績の重複がないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoScheduleDuplicatedRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateNoScheduleDuplicated(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'no_schedule_duplicated');
        $items = Arr::get($this->data, $parameters[0]);

        // 開始時刻と終了時刻が日付でない場合、ここではエラーとしない
        if (!$this->validateDate($attribute, $value['start']) || !$this->validateDate($attribute, $value['end'])) {
            return true;
        }

        $thisStart = Carbon::parse($value['start']);
        $thisEnd = Carbon::parse($value['end']);

        return Seq::fromArray($items)
            ->map(fn (array $item, int $index) => compact('item', 'index'))
            ->forAll(function (array $x) use ($attribute, $thisStart, $thisEnd): bool {
                /** @var int $index */
                $index = $x['index'];
                /** @var array $item */
                $item = $x['item'];
                // 開始時刻と終了時刻が日付でない場合、ここではエラーとしない
                if (!$this->validateDate($attribute, $item['schedule']['start']) || !$this->validateDate($attribute, $item['schedule']['end'])) {
                    return true;
                }

                // 自分自身は検証しない
                if ($index === (int)$this->getExplicitKeys($attribute)[0]) {
                    return true;
                }

                $thatStart = Carbon::parse($item['schedule']['start']);
                $thatEnd = Carbon::parse($item['schedule']['end']);
                return !$thisStart->eq($thatStart) || !$thisEnd->eq($thatEnd);
            });
    }
}

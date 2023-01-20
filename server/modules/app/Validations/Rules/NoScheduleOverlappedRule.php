<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\CarbonRange;
use Illuminate\Support\Arr;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 時間帯が重複する場合かつ合計人数が2人を超える予定または実績がないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoScheduleOverlappedRule
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
    protected function validateNoScheduleOverlapped(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'no_schedule_overlapped');
        $items = Arr::get($this->data, $parameters[0]);

        $index = $this->getExplicitKeys($attribute)[0];

        // 開始時刻または終了時刻が日付でない場合、ここではエラーとしない
        if (!$this->validateDate($attribute, $value['start']) || !$this->validateDate($attribute, $value['end'])) {
            return true;
        }

        // 提供人数が整数でない場合、ここではエラーとしない
        if (!$this->validateInteger($attribute, $items[$index]['headcount'] ?? '')) {
            return true;
        }

        $thisItem = [
            'range' => CarbonRange::create(['start' => $value['start'], 'end' => $value['end']]),
            'headcount' => (int)$items[$index]['headcount'] ?? '',
        ];

        $overlappedItems = Seq::fromArray($items)
            // 自分自身以外を対象とする
            ->flatMap(fn (array $x, int $i): Option => Option::from($i === (int)$index ? null : $x))
            // 開始時刻と終了時刻が正しい日付である場合のみ対象とする
            ->filter(fn (array $x): bool => $this->validateDate($attribute, $x['schedule']['start']) && $this->validateDate($attribute, $x['schedule']['end']))
            // 提供人数が整数である場合のみ対象とする
            ->filter(fn (array $x): bool => $this->validateInteger($attribute, $x['headcount'] ?? ''))
            ->map(fn (array $x): array => [
                'range' => CarbonRange::create($x['schedule']),
                'headcount' => (int)$x['headcount'],
            ])
            ->filter(fn (array $x): bool => $x['range']->isOverlapping($thisItem['range'], false));

        // 完全一致する予実が存在する場合、ここではエラーとしない
        if ($overlappedItems->exists(fn (array $x) => $x['range']->equals($thisItem['range']))) {
            return true;
        }

        // 部分一致 && 合計人数が3人以上となる予実が既に存在している
        if ($overlappedItems->exists(fn (array $x) => $thisItem['headcount'] + $x['headcount'] > 2)) {
            return false;
        }

        // 同じ時間帯に部分一致する予実が既に複数存在している
        // ＝ range との重複部分のみの range 同士で重複する
        $intersections = $overlappedItems->flatMap(fn (array $x) => $x['range']->intersection($thisItem['range']));
        if ($this->compareRecursive($intersections)) {
            return false;
        }

        return true;
    }

    /**
     * 複数の CarbonRange を受け取り、その中に重複が存在するか判定する.
     *
     * @param \Domain\Common\CarbonRange[]|\ScalikePHP\Seq $ranges
     * @return bool
     */
    private function compareRecursive(Seq $ranges): bool
    {
        if ($ranges->size() <= 1) {
            return false;
        } else {
            $head = $ranges->head();
            $tail = $ranges->tail();
            return $tail
                ->exists(fn (CarbonRange $x): bool => $x->isOverlapping($head, false) || $this->compareRecursive($tail));
        }
    }
}

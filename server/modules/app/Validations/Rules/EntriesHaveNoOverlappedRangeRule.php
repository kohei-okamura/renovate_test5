<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Project\LtcsProjectServiceCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 入力値の「介護保険サービス：予実：サービス情報」の中に時間帯の重複がないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait EntriesHaveNoOverlappedRangeRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateEntriesHaveNoOverlappedRange(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'entries_have_no_overlapped_range');
        assert($parameters[0] === 'plans' || $parameters[0] === 'results');

        $index = (int)$this->getExplicitKeys($attribute)[0];
        $entries = Arr::get($this->data, 'entries');

        // 入力値が不正な場合、このバリデーションではエラーとしない
        if (!$this->validateDate($attribute, $value)) {
            return true;
        }
        $thisDate = Carbon::parse($value);

        // slot が不正な場合、このバリデーションではエラーとしない
        if (!$this->validateDateFormat("entries.{$index}.slot.start", $entries[$index]['slot']['start'] ?? '', ['H:i'])
            || !$this->validateDateFormat("entries.{$index}.slot.end", $entries[$index]['slot']['end'] ?? '', ['H:i'])) {
            return true;
        }
        $thisSlot = CarbonRange::create([
            'start' => $entries[$index]['slot']['start'],
            'end' => $entries[$index]['slot']['end'],
        ]);

        // 自費サービスである場合は重複登録が可能なため除く
        if ($entries[$index]['category'] === LtcsProjectServiceCategory::ownExpense()->value()) {
            return true;
        }

        return Seq::fromArray($entries)
            ->map(function (array $x, int $i) use ($index, $thisSlot, $thisDate, $attribute, $parameters): bool {
                // 自分自身は除く
                if ($i === $index) {
                    return true;
                }

                // 比較対象が自費サービスである場合は重複登録が可能なため除く
                if ($x['category'] === LtcsProjectServiceCategory::ownExpense()->value()) {
                    return true;
                }

                // 他の slot が不正な場合、このバリデーションではエラーとしたくないため除く
                if (!$this->validateDateFormat("entries.{$index}.slot.start", $x['slot']['start'] ?? '', ['H:i'])
                    || !$this->validateDateFormat("entries.{$index}.slot.end", $x['slot']['end'] ?? '', ['H:i'])) {
                    return true;
                }
                $thatSlot = CarbonRange::create([
                    'start' => $x['slot']['start'],
                    'end' => $x['slot']['end'],
                ]);

                // 他の slot と時間帯が重複し、かつ日付も同じ場合はエラー
                if ($thatSlot->isOverlapping($thisSlot, false)) {
                    $plans = $x['plans'] ?? [];
                    $results = $x['results'] ?? [];
                    return Seq::fromArray(
                        $parameters[0] === 'plans'
                            ? (is_array($plans) ? $plans : [])
                            : (is_array($results) ? $results : [])
                    )
                        ->forAll(function ($thatDate) use ($attribute, $thisDate): bool {
                            // 他の date が不正な場合、このバリデーションではエラーとしたくないため除く
                            if (!$this->validateDate($attribute, $thatDate)) {
                                return true;
                            }

                            return !Carbon::parse($thatDate)->eq($thisDate);
                        });
                }
                return true;
            })
            ->forAll(fn (bool $b) => $b === true);
    }
}

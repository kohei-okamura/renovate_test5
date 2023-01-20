<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceDuration as Duration;
use Domain\Billing\DwsHomeHelpServiceFragment as Fragment;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）`getDurations` 実装.
 *
 * @mixin \Domain\Billing\DwsHomeHelpServiceChunk
 */
trait DwsHomeHelpServiceChunkGetDurationsMixin
{
    /** {@inheritdoc} */
    public function getDurations(): Seq
    {
        return Seq::from(...$this->generateDurations());
    }

    /**
     * 時間帯別提供情報の一覧を生成する.
     *
     * @return array[]&\Domain\Billing\DwsHomeHelpServiceDuration[][]&iterable
     */
    private function generateDurations(): iterable
    {
        foreach ($this->getDividedFragments() as $index => $fragments) {
            $isSecondary = $index === 1;
            $start = $this->range->start;
            $providedOn = $start->startOfDay();
            $isNotSpanning = $start->isSameDay($this->range->end);
            $isSpanning = !$isNotSpanning;
            $spanningDuration = $isSpanning
                ? self::computeSpanningDuration($this->category, $providedOn, $fragments, isFirst: true)
                : 0;
            $durations = $fragments->flatMap(fn (Fragment $fragment): iterable => self::generateDurationsRecursive(
                $this->category,
                $fragment,
                $providedOn,
                $fragment->range->start,
                Timeframe::fromHour($fragment->range->start->hour),
                $isSecondary,
                $isSpanning,
                $spanningDuration,
                isFirst: true
            ));
            // `$durations` を計算しておかないと GitHub Actions など一部のケースで正常に動作しない……かもしれない
            $computedDurations = Seq::from(...$durations);
            yield [...self::composeRecursive($computedDurations)];
        }
    }

    /**
     * 1人目のみ・2人目のみの要素一覧を返す.
     *
     * @return \Domain\Billing\DwsHomeHelpServiceFragment[][]&iterable&\ScalikePHP\Seq[]
     */
    private function getDividedFragments(): iterable
    {
        // 1人目
        yield 0 => $this->fragments->filterNot(fn (Fragment $x): bool => $x->isSecondary);

        // 2人目
        $secondary = $this->fragments->filter(fn (Fragment $x): bool => $x->isSecondary || $x->headcount === 2);
        if ($secondary->nonEmpty()) {
            yield 1 => $secondary;
        }
    }

    /**
     * 再帰的に時間帯別提供情報の一覧を生成する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\Billing\DwsHomeHelpServiceFragment $fragment
     * @param \Domain\Common\Carbon $providedOn
     * @param \Domain\Common\Carbon $durationStart
     * @param \Domain\ServiceCodeDictionary\Timeframe $timeframe
     * @param bool $isSecondary
     * @param bool $isSpanning
     * @param int $spanningDuration
     * @param bool $isFirst 一連のサービスにおける最初の時間帯かどうか
     * @return \Domain\Billing\DwsHomeHelpServiceDuration[]&iterable
     */
    private static function generateDurationsRecursive(
        DwsServiceCodeCategory $category,
        Fragment $fragment,
        Carbon $providedOn,
        Carbon $durationStart,
        Timeframe $timeframe,
        bool $isSecondary,
        bool $isSpanning,
        int $spanningDuration,
        bool $isFirst
    ): iterable {
        $duration = self::getDurationForTimeframe(
            $category,
            $timeframe,
            $durationStart,
            $fragment->range->end,
            $fragment->providerType,
            $isFirst
        );
        if ($duration > 0) {
            $isSecondDay = $isSpanning && !$durationStart->isSameDay($providedOn);
            yield DwsHomeHelpServiceDuration::create([
                'category' => $category,
                'providerType' => $fragment->providerType,
                'isSecondary' => $isSecondary,
                'isSpanning' => $isSecondDay,
                'spanningDuration' => $isSecondDay ? $spanningDuration : 0,
                'providedOn' => $isSecondDay ? $providedOn->addDay() : $providedOn,
                'timeframe' => $timeframe,
                'duration' => $duration,
                'headcount' => 1,
            ]);
        }
        $nextDurationStart = $durationStart->addMinutes($duration);
        $isDurationRemains = $nextDurationStart < $fragment->range->end;
        if ($isDurationRemains) {
            yield from self::generateDurationsRecursive(
                $category,
                $fragment,
                $providedOn,
                $nextDurationStart,
                $duration === 0 ? $timeframe->next() : Timeframe::fromHour($nextDurationStart->hour),
                $isSecondary,
                $isSpanning,
                $spanningDuration,
                $isFirst && $duration <= 0
            );
        }
    }

    /**
     * 次の時間帯区切り位置を取得する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\ServiceCodeDictionary\Timeframe $timeframe
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $type
     * @param bool $isFirst 一連のサービスにおける最初の時間帯かどうか
     * @return int
     */
    private static function getDurationForTimeframe(
        DwsServiceCodeCategory $category,
        Timeframe $timeframe,
        Carbon $start,
        Carbon $end,
        DwsHomeHelpServiceProviderType $type,
        bool $isFirst
    ): int {
        $durationMinutes = $start->diffInMinutes($end);

        // 最小単位を考慮しない時間帯の区切り位置とそこまでの時間数（分）
        $nextBoundaryHour = $timeframe->nextBoundaryHour();
        $nextBoundary = $start->hour($nextBoundaryHour)->minute(0);
        $nextBoundaryMinutes = $start->diffInMinutes($nextBoundary);

        // 時間帯が深夜＆時刻が21時以降である場合
        // -> 時間帯区切り位置＝1日の区切り位置となるため1日の区切り位置までの時間を返す
        if ($timeframe === Timeframe::midnight() && $start->hour >= 21) {
            return $start->diffInMinutes(min($end, $category->getDayBoundary($start, $type, $isFirst)));
        }

        // 最小単位を考慮しない時間帯の区切り位置より前にサービスが終了する場合
        // -> サービス終了までの時間数を返す
        if ($durationMinutes <= $nextBoundaryMinutes) {
            return $durationMinutes;
        }

        $minDurationMinutesForStart = $category->getMinDurationMinutes($type, isFirst: true);
        $minDurationMinutes = $category->getMinDurationMinutes($type, isFirst: false);

        // 最初の時間帯に最小単位で時間帯を跨ぐ場合
        // -> 当該時間帯が占める割合が少ない（50%未満）の場合は当該時間帯では算定しないため 0 を返す
        if ($isFirst && $nextBoundaryMinutes < $minDurationMinutesForStart) {
            $actualDurationMinutes = min($durationMinutes, $minDurationMinutesForStart);
            $threshold = $actualDurationMinutes / 2;
            return $nextBoundaryMinutes < $threshold ? 0 : $actualDurationMinutes;
        }

        // 最小単位に満たない時間数
        // `$fraction` が負の値になると動作がおかしくなるがその場合は1つ前の `if` 文で既に `return` しているはず
        $fraction = $isFirst
            ? ($nextBoundaryMinutes - $minDurationMinutesForStart) % $minDurationMinutes
            : $nextBoundaryMinutes % $minDurationMinutes;
        if ($fraction === 0) {
            // 最小単位で割り切れる場合＝最小単位での時間帯跨ぎが発生しない場合
            // -> そのまま返す
            return $nextBoundaryMinutes;
        } else {
            // 最小単位で割り切れない場合＝最小単位での時間帯跨ぎが発生する場合
            // -> 最後の30分の内、占める割合が多い方の時間帯で算定されるよう調整して返す
            return $fraction >= $minDurationMinutes / 2
                ? min($durationMinutes, $nextBoundaryMinutes + $minDurationMinutes - $fraction)
                : min($durationMinutes, $nextBoundaryMinutes - $fraction);
        }
    }

    /**
     * 日跨ぎ時間数（1日目の時間数）を算出する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\Common\Carbon $providedOn
     * @param \Domain\Billing\DwsHomeHelpServiceFragment[]&\ScalikePHP\Seq $fragments
     * @param bool $isFirst
     * @return int
     */
    private static function computeSpanningDuration(
        DwsServiceCodeCategory $category,
        Carbon $providedOn,
        Seq $fragments,
        bool $isFirst
    ): int {
        if ($fragments->isEmpty()) {
            return 0;
        }

        /** @var \Domain\Billing\DwsHomeHelpServiceFragment $head */
        $head = $fragments->head();
        $tail = $fragments->drop(1);

        if ($head->range->end->isSameDay($providedOn)) {
            // 終了日時がサービス提供日（1日目）に一致する場合：全時間数を計算 + 残りを計算
            return $head->range->durationMinutes() + self::computeSpanningDuration($category, $providedOn, $tail, isFirst: false);
        } elseif ($head->range->start->isSameDay($providedOn)) {
            // 開始日時がサービス提供日（1日目）に一致する場合：日を跨いでいるので区切り位置までの時間数を計算 + 残りを計算
            $dayBoundary = $category->getDayBoundary($head->range->start, $head->providerType, $isFirst);
            $duration = $head->range->start->diffInMinutes($dayBoundary);
            return $duration + self::computeSpanningDuration($category, $providedOn, $tail, isFirst: false);
        } else {
            // 開始日時・終了日時が共にサービス提供日（1日目）と異なる場合：日跨ぎ時間数には含めない
            return self::computeSpanningDuration($category, $providedOn, $tail, isFirst: false);
        }
    }

    /**
     * 家事援助かどうかを判定する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return bool
     */
    private static function isHousework(DwsServiceCodeCategory $category): bool
    {
        return $category === DwsServiceCodeCategory::housework();
    }

    /**
     * 同条件の時間帯別提供情報を再帰的に合成する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @param int $adjustment
     * @return \Domain\Billing\DwsHomeHelpServiceDuration[]&iterable
     */
    private static function composeRecursive(Seq $durations, int $adjustment = 0): iterable
    {
        assert($durations->nonEmpty());

        /** @var \Domain\Billing\DwsHomeHelpServiceDuration $head */
        $head = $durations->head();
        $tail = $durations->drop(1);

        if ($tail->isEmpty()) {
            // 後続する時間帯別提供情報がない場合は先頭の時間帯別提供情報を返して終了
            yield $head->copy([
                'duration' => $head->duration + $adjustment,
            ]);
        } else {
            // 連続する要素が合成可能な場合のみ合成を行う
            /** @var \Domain\Billing\DwsHomeHelpServiceDuration $next */
            $next = $tail->head();
            $isComposable = $next->category === $head->category
                && $next->providerType === $head->providerType
                && $next->isSecondary === $head->isSecondary
                && $next->isSpanning === $head->isSpanning
                && $next->providedOn->eq($head->providedOn)
                && $next->timeframe === $head->timeframe
                && $next->headcount === $head->headcount;
            if ($isComposable) {
                // 合成後の時間帯別提供情報が更に合成可能な場合があるため再帰的に処理を行う
                // 合成後の時間数が最小単位（30分）を跨ぐ場合は調整を行う
                $minDurationMinutes = $head->category->getMinDurationMinutes($head->providerType, isFirst: false);
                $composedDuration = $head->duration + $next->duration + $adjustment;
                $fraction = $composedDuration % $minDurationMinutes;
                $remains = $tail->drop(1);
                $hasRemains = $remains->nonEmpty();
                if ($fraction === 0 || !$hasRemains) {
                    // 最小単位で割り切れる場合＝最小単位での時間帯跨ぎが発生しない場合
                    // or 後続の要素（Fragment）がもうない場合
                    // -> 調整は行わない
                    $composed = Seq::from(
                        $head->copy([
                            'spanningDuration' => $head->spanningDuration + $next->spanningDuration,
                            'duration' => $composedDuration,
                        ]),
                        ...$remains
                    );
                    yield from self::composeRecursive($composed);
                } elseif ($fraction >= $minDurationMinutes / 2) {
                    // 最小単位で割り切れない場合＝最小単位での時間帯跨ぎが発生する場合
                    // and 余りが最小単位の50%以上を占める場合
                    // -> 現在処理している時間帯で算定されるよう調整する
                    $newAdjustment = min(
                        $minDurationMinutes - $fraction,
                        $remains->map(fn (Duration $x): int => $x->duration)->sum()
                    );
                    $composed = Seq::from(
                        $head->copy([
                            'spanningDuration' => $head->spanningDuration + $next->spanningDuration,
                            'duration' => $composedDuration + $newAdjustment,
                        ]),
                        ...$remains
                    );
                    yield from self::composeRecursive($composed, -$newAdjustment);
                } else {
                    // 最小単位で割り切れない場合＝最小単位での時間帯跨ぎが発生する場合
                    // and 余りが最小単位の50%未満となる場合
                    // -> 余りが次の時間帯で算定されるよう調整する
                    $composed = Seq::from(
                        $head->copy([
                            'spanningDuration' => $head->spanningDuration + $next->spanningDuration,
                            'duration' => $composedDuration - $fraction,
                        ]),
                        ...$remains
                    );
                    yield from self::composeRecursive($composed, $fraction);
                }
            } else {
                // 先頭の時間帯別提供情報は合成不可能なため後続の時間帯別提供情報の合成を試みる
                yield $head;
                yield from self::composeRecursive($tail, $adjustment);
            }
        }
    }
}

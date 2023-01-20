<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdDuration as Duration;
use Domain\Billing\DwsVisitingCareForPwsdFragment as Fragment;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護）`getDurations` 実装.
 *
 * @mixin \Domain\Billing\DwsVisitingCareForPwsdChunk
 */
trait DwsVisitingCareForPwsdChunkGetDurationsMixin
{
    use DwsVisitingCareForPwsdChunkGetDayBoundaryMixin;

    /** {@inheritdoc} */
    public function getDurations(): Seq
    {
        return Seq::from(...$this->generateDurations());
    }

    /**
     * 時間帯別提供情報の一覧を生成する.
     *
     * @return array[]|\Domain\Billing\DwsVisitingCareForPwsdDuration[][]|iterable
     */
    private function generateDurations(): iterable
    {
        foreach ($this->getDividedFragments() as $fragments) {
            $isFirstHour = true;
            $durations = $fragments
                ->flatMap(function (Fragment $fragment) use (&$isFirstHour): iterable {
                    $xs = self::generateDurationsRecursive(
                        $this->category,
                        $fragment,
                        $fragment->range->start,
                        Timeframe::fromHour($fragment->range->start->hour),
                        $isFirstHour
                    );
                    $isFirstHour = false;
                    return $xs;
                })
                ->computed();
            $composed = Seq::from(...self::composeRecursive($durations));
            [$movingDurations, $nonMovingDurations] = $composed->partition(fn (Duration $x): bool => $x->isMoving);
            yield [
                ...($nonMovingDurations->isEmpty() ? [] : self::adjustRecursive($nonMovingDurations)),
                ...($movingDurations->isEmpty() ? [] : self::adjustRecursive($movingDurations)),
            ];
        }
    }

    /**
     * 1人目のみ・2人目のみの要素一覧を返す.
     *
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment[][]|iterable|\ScalikePHP\Seq[]
     */
    private function getDividedFragments(): iterable
    {
        // 1人目
        yield $this->fragments->filterNot(fn (Fragment $x): bool => $x->isSecondary);

        // 2人目
        $secondary = $this->fragments->filter(fn (Fragment $x): bool => $x->headcount === 2 || $x->isSecondary);
        if ($secondary->nonEmpty()) {
            yield $secondary->map(fn (Fragment $x): Fragment => $x->copy(['isSecondary' => true]));
        }
    }

    /**
     * 再帰的に時間帯別提供情報の一覧を生成する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment $fragment
     * @param \Carbon\CarbonImmutable|\Domain\Common\Carbon $durationStart
     * @param \Domain\ServiceCodeDictionary\Timeframe $timeframe
     * @param bool $isFirstHour
     * @param int $offsetMovingDurationMinutes
     * @return \Domain\Billing\DwsVisitingCareForPwsdDuration[]|iterable
     */
    private static function generateDurationsRecursive(
        DwsServiceCodeCategory $category,
        Fragment $fragment,
        Carbon $durationStart,
        Timeframe $timeframe,
        bool $isFirstHour,
        int $offsetMovingDurationMinutes = 0
    ): iterable {
        $serviceDurationMinutes = self::getDurationForTimeframe(
            $timeframe,
            $durationStart,
            $fragment->range->end,
            $isFirstHour
        );
        $duration = $fragment->isMoving
            ? min($fragment->movingDurationMinutes - $offsetMovingDurationMinutes, $serviceDurationMinutes)
            : $serviceDurationMinutes;
        if ($duration > 0) {
            yield DwsVisitingCareForPwsdDuration::create([
                'category' => $category,
                'isHospitalized' => $fragment->isHospitalized,
                'isLongHospitalized' => $fragment->isLongHospitalized,
                'isCoaching' => $fragment->isCoaching,
                'isMoving' => $fragment->isMoving,
                'isSecondary' => $fragment->isSecondary,
                'providedOn' => $durationStart->startOfDay(),
                'timeframe' => $fragment->isMoving ? Timeframe::unknown() : $timeframe,
                'duration' => $duration,
                'headcount' => $fragment->headcount,
            ]);
        }
        $nextDurationStart = $durationStart->addMinutes($duration);
        $nextOffsetMinutes = $fragment->isMoving ? $offsetMovingDurationMinutes + $duration : 0;
        $isDurationRemains = $nextDurationStart < $fragment->range->end
            && (!$fragment->isMoving || $nextOffsetMinutes < $fragment->movingDurationMinutes);
        if ($isDurationRemains) {
            yield from self::generateDurationsRecursive(
                $category,
                $fragment,
                $nextDurationStart,
                $timeframe->next(),
                false,
                $nextOffsetMinutes
            );
        }
    }

    /**
     * 次の時間帯区切り位置を取得する.
     *
     * @param \Domain\ServiceCodeDictionary\Timeframe $timeframe
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param bool $isFirstHour
     * @return int
     */
    private static function getDurationForTimeframe(
        Timeframe $timeframe,
        Carbon $start,
        Carbon $end,
        bool $isFirstHour
    ): int {
        if ($timeframe === Timeframe::midnight() && $start->hour >= 21) {
            // 時間帯が深夜＆時刻が21時以降である場合
            // -> 時間帯区切り位置＝1日の区切り位置となるため1日の区切り位置を返す
            return $start->diffInMinutes(min($end, self::getDayBoundary($start)));
        }
        $durationMinutes = $start->diffInMinutes($end);
        $nextBoundaryHour = $timeframe->nextBoundaryHour();
        $nextBoundary = $start->hour($nextBoundaryHour)->minute(0);
        $nextBoundaryMinutes = $start->diffInMinutes($nextBoundary);

        if ($durationMinutes <= $nextBoundaryMinutes) {
            // 時間帯の区切り位置より前にサービスが終了する場合 -> サービス終了までの時間数を返す
            return $durationMinutes;
        }

        if ($isFirstHour && $nextBoundaryMinutes < self::MIN_DURATION_MINUTES_OF_FIRST_HOUR) {
            // 最初の1時間（最小単位）で時間帯を跨ぐ場合
            // -> 当該時間帯が占める割合が少ない（50%未満）の場合は当該時間帯では算定しないため 0 を返す
            $actualDurationMinutes = min($durationMinutes, self::MIN_DURATION_MINUTES_OF_FIRST_HOUR);
            $threshold = $actualDurationMinutes / 2;
            return $nextBoundaryMinutes < $threshold ? 0 : $actualDurationMinutes;
        } else {
            return min($durationMinutes, $nextBoundaryMinutes);
        }
    }

    /**
     * 同条件の時間帯別提供情報を再帰的に合成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration[]|\ScalikePHP\Seq $durations
     * @return \Domain\Billing\DwsVisitingCareForPwsdDuration[]|iterable
     */
    private static function composeRecursive(Seq $durations): iterable
    {
        assert($durations->nonEmpty());

        /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $head */
        $head = $durations->head();
        $tail = $durations->tail();

        if ($tail->isEmpty()) {
            // 後続する時間帯別提供情報がない場合は先頭の時間帯別提供情報を返して終了
            yield $head;
        } else {
            // 連続する要素が合成可能な場合のみ合成を行う
            /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $next */
            $next = $tail->head();
            $isComposable = $next->category === $head->category
                && $next->isHospitalized === $head->isHospitalized
                && $next->isLongHospitalized === $head->isLongHospitalized
                && $next->isCoaching === $head->isCoaching
                && $next->isMoving === $head->isMoving
                && $next->isSecondary === $head->isSecondary
                && $next->providedOn->eq($head->providedOn)
                && $next->timeframe === $head->timeframe
                && $next->headcount === $head->headcount;
            if ($isComposable) {
                // 合成後の時間帯別提供情報が更に合成可能な場合があるため再帰的に処理を行う
                $composed = Seq::from(
                    $head->copy(['duration' => $head->duration + $next->duration]),
                    ...$tail->tail(),
                );
                yield from self::composeRecursive($composed);
            } else {
                // 先頭の時間帯別提供情報は合成不可能なため後続の時間帯別提供情報の合成を試みる
                yield $head;
                yield from self::composeRecursive($tail);
            }
        }
    }

    /**
     * 最小単位（30分）ごとに割り切れるよう再帰的に調整する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration[]|\ScalikePHP\Seq $durations
     * @param bool $isFirstDuration
     * @return \Domain\Billing\DwsVisitingCareForPwsdDuration[]|iterable
     */
    private function adjustRecursive(Seq $durations, bool $isFirstDuration = true): iterable
    {
        assert($durations->nonEmpty());

        /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $head */
        $head = $durations->head();
        $tail = $durations->tail();

        if ($tail->isEmpty()) {
            // 後続する時間帯別提供情報がない場合は先頭の時間帯別提供情報を返して終了
            yield $head;
        } elseif ($isFirstDuration) {
            if ($head->duration >= self::MIN_DURATION_MINUTES_OF_FIRST_HOUR) {
                // 通常の最小単位（30分）で割り切れれば OK なのでフラグを OFF にして再帰的に処理する
                yield from self::adjustRecursive($durations, false);
            } else {
                /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $next */
                $next = $tail->head();
                if ($head->duration < self::MIN_DURATION_MINUTES_OF_FIRST_HOUR / 2) {
                    // 当該時間帯での算定を行わないため次の時間帯に合算する
                    // 調整後の時間帯別提供情報が再度調整を要する場合があるため再帰的に処理を行う
                    $adjusted = Seq::from(
                        $next->copy(['duration' => $next->duration + $head->duration]),
                        ...$tail->tail(),
                    );
                    yield from self::adjustRecursive($adjusted, $isFirstDuration);
                } else {
                    $shortage = self::MIN_DURATION_MINUTES_OF_FIRST_HOUR - $head->duration;
                    if ($next->duration <= $shortage) {
                        // 次の時間帯をまるごと合算する
                        // 調整後の時間帯別提供情報が再度調整を要する場合があるため再帰的に処理を行う
                        $adjusted = Seq::from(
                            $head->copy(['duration' => $next->duration + $head->duration]),
                            ...$tail->tail(),
                        );
                        yield from self::adjustRecursive($adjusted, $isFirstDuration);
                    } else {
                        // 次の時間帯から不足分を補う
                        yield $head->copy(['duration' => self::MIN_DURATION_MINUTES_OF_FIRST_HOUR]);
                        $adjusted = Seq::from(
                            $next->copy(['duration' => $next->duration - $shortage]),
                            ...$tail->tail(),
                        );
                        yield from self::adjustRecursive($adjusted, false);
                    }
                }
            }
        } else {
            /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $next */
            $next = $tail->head();
            $surplus = $head->duration % self::MIN_DURATION_MINUTES;
            if ($surplus === 0) {
                // 最小単位（30分）で割り切れる場合は調整不要
                yield $head;
                yield from self::adjustRecursive($tail, false);
            } elseif ($surplus < self::MIN_DURATION_MINUTES / 2) {
                // 余りを次の時間帯に合算する
                // 調整後の時間帯別提供情報が再度調整を要する場合があるため再帰的に処理を行う
                yield $head->copy(['duration' => $head->duration - $surplus]);
                $adjusted = Seq::from(
                    $next->copy(['duration' => $next->duration + $surplus]),
                    ...$tail->tail(),
                );
                yield from self::adjustRecursive($adjusted, false);
            } elseif ($next->duration <= $surplus) {
                // 次の時間帯をすべて合算する
                // 調整後の時間帯別提供情報が再度調整を要する場合があるため再帰的に処理を行う
                $adjusted = Seq::from(
                    $head->copy(['duration' => $head->duration + $next->duration]),
                    ...$tail->tail(),
                );
                yield from self::adjustRecursive($adjusted, false);
            } else {
                // 次の時間帯から不足分を補う
                // 調整後の時間帯別提供情報が再度調整を要する場合があるため再帰的に処理を行う
                $distribution = self::MIN_DURATION_MINUTES - $surplus;
                yield $head->copy(['duration' => $head->duration + $distribution]);
                $adjusted = Seq::from(
                    $next->copy(['duration' => $next->duration - $distribution]),
                    ...$tail->tail(),
                );
                yield from self::adjustRecursive($adjusted, false);
            }
        }
    }
}

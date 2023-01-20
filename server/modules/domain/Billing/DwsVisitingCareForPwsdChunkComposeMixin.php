<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Closure;
use Domain\Billing\DwsVisitingCareForPwsdChunk as Chunk;
use Domain\Billing\DwsVisitingCareForPwsdFragment as Fragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護） `compose` 実装.
 *
 * @mixin \Domain\Billing\DwsVisitingCareForPwsdChunk
 */
trait DwsVisitingCareForPwsdChunkComposeMixin
{
    /** {@inheritdoc} */
    public function compose(Chunk $that): Chunk
    {
        if (!$this->isComposable($that)) {
            throw new LogicException('that is not composable entity.');
        }

        assert($that->fragments->count() <= 2);

        $isNotMoving = fn (Fragment $x): bool => !$x->isMoving;
        $isMoving = fn (Fragment $x): bool => $x->isMoving;
        $fragments = $this->range->isOverlapping($that->range)
            ? Seq::from(
                ...$this->composeFragments($that, $isNotMoving),
                ...$this->composeFragments($that, $isMoving),
            )
            : Seq::from(
                ...$this->fragments,
                ...$that->fragments,
            );

        $range = CarbonRange::create([
            'start' => $fragments->map(fn (Fragment $x): Carbon => $x->range->start)->min(),
            'end' => $fragments->map(fn (Fragment $x): Carbon => $x->range->end)->max(),
        ]);

        return $this->copy(compact('range', 'fragments'));
    }

    /** {@inheritdoc} */
    public function isComposable(Chunk $that): bool
    {
        return ($this->category === $that->category)
            && ($this->providedOn->eq($that->providedOn));
    }

    /**
     * 合成した Fragments を得る
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $that
     * @param \Closure $filter
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment[]|iterable
     */
    private function composeFragments(Chunk $that, Closure $filter): iterable
    {
        $thisFragments = $this->fragments->filter($filter);
        $thatFragments = $that->fragments->filter($filter);

        if ($thisFragments->isEmpty() && $thatFragments->nonEmpty()) {
            return $thatFragments;
        }
        if ($thatFragments->isEmpty() && $thisFragments->nonEmpty()) {
            return $thisFragments;
        }
        return $thatFragments->fold($thisFragments, function (Seq $z, $thatFragment): Seq {
            return Seq::from(...self::composeFragmentsRecursive($z, $thatFragment))
                ->sortBy(fn (Fragment $x): Carbon => $x->range->start);
        });
    }

    /**
     * 再帰的に合成された要素の一覧を返す.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment|iterable $fragments
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment $thatFragment
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment|iterable
     */
    private static function composeFragmentsRecursive(iterable $fragments, Fragment $thatFragment): iterable
    {
        $thisFragments = Seq::from(...$fragments);
        return $thisFragments
            ->find(fn (Fragment $thisFragment): bool => $thisFragment->isComposable($thatFragment))
            ->map(function (Fragment $thisFragment) use ($thatFragment, $thisFragments): iterable {
                $thisFragmentIndex = array_search($thisFragment, $thisFragments->toArray(), true);
                $unprocessedFragments = [
                    ...$thisFragments->take($thisFragmentIndex),
                    ...$thisFragments->drop($thisFragmentIndex + 1),
                ];

                // 時間範囲と移動加算対象時間数が完全に一致する場合は人数を増やして終了
                if ($thisFragment->hasSameDuration($thatFragment)) {
                    $composed = $thisFragment->copy([
                        'headcount' => $thisFragment->headcount + $thatFragment->headcount,
                    ]);
                    return [$composed, ...$unprocessedFragments];
                }

                $totalMovingDurationMinutes =
                    $thisFragment->movingDurationMinutes
                    + $thatFragment->movingDurationMinutes;

                // 時間範囲が連続している場合は1つの要素に合成する
                // 合成した要素がさらに他の要素と合成される場合があるため再帰的に処理を行う.
                if ($thisFragment->range->isConsecutive($thatFragment->range)) {
                    // 人数が一致する時間範囲が連続している場合は1つの要素に合成する.
                    // 合成した要素がさらに他の要素と合成される場合があるため再帰的に処理を行う.
                    if ($thisFragment->headcount === $thatFragment->headcount) {
                        $composed = $thisFragment->copy([
                            'movingDurationMinutes' => $totalMovingDurationMinutes,
                            'range' => CarbonRange::create([
                                'start' => min($thisFragment->range->start, $thatFragment->range->start),
                                'end' => max($thisFragment->range->end, $thatFragment->range->end),
                            ]),
                        ]);
                        return self::composeFragmentsRecursive($unprocessedFragments, $composed);
                    }

                    // 人数が異なる時間範囲が連続している場合は2人を分離し、分離した一人目とthatを合成する.
                    // 合成した要素がさらに他の要素と合成される場合があるため再帰的に処理を行う.
                    if ($thisFragment->headcount === 2) {
                        $isolated = $thisFragment->copy(['headcount' => 1, 'isSecondary' => true]);
                        $composed = $thisFragment->copy([
                            'headcount' => 1,
                            'isSecondary' => false,
                            'range' => CarbonRange::create([
                                'start' => min($thisFragment->range->start, $thatFragment->range->start),
                                'end' => max($thisFragment->range->end, $thatFragment->range->end),
                            ]),
                        ]);
                    } else {
                        $isolated = $thatFragment->copy(['headcount' => 1, 'isSecondary' => true]);
                        $composed = $thatFragment->copy([
                            'headcount' => 1,
                            'isSecondary' => false,
                            'range' => CarbonRange::create([
                                'start' => min($thatFragment->range->start, $thisFragment->range->start),
                                'end' => max($thatFragment->range->end, $thisFragment->range->end),
                            ]),
                        ]);
                    }
                    return [
                        $isolated,
                        ...self::composeFragmentsRecursive($unprocessedFragments, $composed),
                    ];
                }

                // 重複している範囲のみ2人目がいるため合成と分離を行う
                // 合成した要素がさらに他の要素と合成される場合があるため再帰的に処理を行う.
                $isolatedRange = CarbonRange::create([
                    'start' => max($thisFragment->range->start, $thatFragment->range->start),
                    'end' => min($thisFragment->range->end, $thatFragment->range->end),
                ]);
                $composedRange = CarbonRange::create([
                    'start' => min($thisFragment->range->start, $thatFragment->range->start),
                    'end' => max($thisFragment->range->end, $thatFragment->range->end),
                ]);
                $isolatedMovingDurationMinutes = min(
                    $isolatedRange->durationMinutes(),
                    $thatFragment->movingDurationMinutes
                );
                $composedMovingDurationMinutes = $totalMovingDurationMinutes - $isolatedMovingDurationMinutes;
                $isolated = $thatFragment->copy([
                    'isSecondary' => true,
                    'movingDurationMinutes' => $isolatedMovingDurationMinutes,
                    'range' => $isolatedRange,
                ]);
                $composed = $thisFragment->copy([
                    'isSecondary' => false,
                    'movingDurationMinutes' => $composedMovingDurationMinutes,
                    'range' => $composedRange,
                ]);
                return [
                    $isolated,
                    ...self::composeFragmentsRecursive($unprocessedFragments, $composed),
                ];
            })
            // 合成可能な要素がない場合は合成せずに終了
            ->getOrElse(fn (): iterable => [...$thisFragments, $thatFragment]);
    }
}

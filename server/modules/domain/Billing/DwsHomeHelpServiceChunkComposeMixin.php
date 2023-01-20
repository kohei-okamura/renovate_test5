<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceFragment as Fragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護） `compose` 実装.
 *
 * @mixin \Domain\Billing\DwsHomeHelpServiceChunk
 */
trait DwsHomeHelpServiceChunkComposeMixin
{
    /** {@inheritdoc} */
    public function compose(DwsHomeHelpServiceChunk $that): self
    {
        if ($that->fragments->count() !== 1) {
            throw new LogicException('Invalid fragments of that');
        } elseif (!$this->isComposable($that)) {
            throw new LogicException('that is not composable entity.');
        } elseif ($this->range->end <= $that->range->start) {
            // 重複なし
            $fragments = $this->composeFragmentsSimple($that);
            return $this->withFragments($fragments);
        } elseif ($this->range->isOverlapping($that->range)) {
            // 時間が重複
            $fragments = $this->composeFragmentsRecursive($this->fragments, $that->fragments[0]);
            return $this->withFragments($fragments);
        } else {
            // 到達不能コード
            throw new LogicException('Invalid situations.'); // @codeCoverageIgnore
        }
    }

    /** {@inheritdoc} */
    public function isComposable(DwsHomeHelpServiceChunk $that): bool
    {
        return ($this->category === $that->category)
            && ($this->buildingType === $that->buildingType)
            && ($this->isEmergency === false && $that->isEmergency === false)
            && ($this->isPlannedByNovice === $that->isPlannedByNovice)
            && ($this->range->copy(['end' => $this->range->end->addHours(2)])->isOverlapping($that->range));
    }

    /**
     * 新しい要素（Fragment）を指定してサービス単位を生成する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceFragment[]|iterable $fragments
     * @return self
     */
    private function withFragments(iterable $fragments): self
    {
        $fragmentsSeq = Seq::from(...$fragments);
        $ranges = Seq::from(...$fragmentsSeq->map(fn (Fragment $x): CarbonRange => $x->range));
        return $this->copy([
            'range' => CarbonRange::create([
                'start' => $ranges->map(fn (CarbonRange $x): Carbon => $x->start)->min(),
                'end' => $ranges->map(fn (CarbonRange $x): Carbon => $x->end)->max(),
            ]),
            'fragments' => $fragmentsSeq,
        ]);
    }

    /**
     * 時間重複していない要素（Fragment）を組み立てる（2時間ルール）.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $that
     * @throws \Exception
     * @return \Domain\Billing\DwsHomeHelpServiceFragment[]|iterable
     */
    private function composeFragmentsSimple(DwsHomeHelpServiceChunk $that): iterable
    {
        // 人数が一致しない場合は1人ずつの要素（Fragment）に分解して合成する
        $isHeadcountMismatch = $this->fragments->exists(
            fn (Fragment $x): bool => $x->headcount !== $that->fragments[0]->headcount
        );
        if ($isHeadcountMismatch) {
            return $this->fragments
                ->append($that->fragments)
                ->flatMap(function (Fragment $fragment): iterable {
                    // 2人の場合に2つの要素（Fragment）に分割する
                    return $fragment->headcount === 2
                        ? [
                            $fragment->copy(['headcount' => 1, 'isSecondary' => false]),
                            $fragment->copy(['headcount' => 1, 'isSecondary' => true]),
                        ]
                        : [$fragment];
                });
        }

        // 資格が一致しない場合は単純に要素（Fragment）を全て含める
        $isProviderTypeMismatch = $this->fragments->exists(
            fn (Fragment $x): bool => $x->providerType !== $that->fragments[0]->providerType
        );
        if ($isProviderTypeMismatch) {
            return $this->fragments->append($that->fragments);
        }

        // 時間が連続している場合は時間が延びるように合成する
        if ($this->range->end->equalTo($that->range->start)) {
            $fragmentArray = $this->fragments->toArray();
            $lastFragment = $this->fragments->maxBy(fn (Fragment $x): Carbon => $x->range->end);
            $lastFragmentIndex = array_search($lastFragment, $fragmentArray, true);
            return [
                ...$this->fragments->take($lastFragmentIndex),
                $lastFragment->copy([
                    'range' => $lastFragment->range->copy(['end' => $that->range->end]),
                ]),
                ...$this->fragments->drop($lastFragmentIndex + 1),
            ];
        }

        // 不連続（2時間ルール）
        return $this->fragments->append($that->fragments);
    }

    /**
     * 時間重複している要素（Fragment）を組み立てる.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceFragment[]|iterable $fragments
     * @param \Domain\Billing\DwsHomeHelpServiceFragment $thatFragment 合成する Fragment
     * @return \Domain\Billing\DwsHomeHelpServiceFragment[]|iterable
     */
    private static function composeFragmentsRecursive(iterable $fragments, Fragment $thatFragment): iterable
    {
        $thisFragments = Seq::from(...$fragments);
        return $thisFragments
            ->find(fn (Fragment $thisFragment): bool => $thisFragment->isComposable($thatFragment))
            ->map(function (Fragment $thisFragment) use ($thisFragments, $thatFragment): iterable {
                $thisFragmentIndex = array_search($thisFragment, $thisFragments->toArray(), true);
                $unprocessedFragments = [
                    ...$thisFragments->take($thisFragmentIndex),
                    ...$thisFragments->drop($thisFragmentIndex + 1),
                ];

                // 時間範囲が完全に一致する場合は人数を増やして終了
                if ($thisFragment->hasSameDuration($thatFragment)) {
                    $composed = $thisFragment->copy([
                        'headcount' => $thisFragment->headcount + $thatFragment->headcount,
                    ]);
                    return [$composed, ...$unprocessedFragments];
                }

                // 時間範囲が連続している場合は1つの要素に合成する
                // 合成した要素がさらに他の要素と合成される場合があるため再帰的に処理を行う.
                if ($thisFragment->range->isConsecutive($thatFragment->range)) {
                    $composed = $thisFragment->copy([
                        'range' => CarbonRange::create([
                            'start' => min($thisFragment->range->start, $thatFragment->range->start),
                            'end' => max($thisFragment->range->end, $thatFragment->range->end),
                        ]),
                    ]);
                    return self::composeFragmentsRecursive($unprocessedFragments, $composed);
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
                $isolated = $thatFragment->copy([
                    'isSecondary' => true,
                    'range' => $isolatedRange,
                ]);
                $composed = $thisFragment->copy([
                    'isSecondary' => false,
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

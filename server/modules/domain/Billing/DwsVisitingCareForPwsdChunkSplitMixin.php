<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdFragment as Fragment;
use Domain\Common\Carbon;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護） `split` 実装.
 *
 * @mixin \Domain\Billing\DwsVisitingCareForPwsdChunk
 */
trait DwsVisitingCareForPwsdChunkSplitMixin
{
    use DwsVisitingCareForPwsdChunkGetDayBoundaryMixin;

    /** {@inheritdoc} */
    public function split(): Seq
    {
        $start = $this->range->start;
        $end = $this->range->end;
        $dayOfStart = $start->startOfDay();
        $dayOfEnd = $end->startOfDay();

        // 開始日と終了日が一致する場合は分割しない
        if ($dayOfStart->eq($dayOfEnd)) {
            return Seq::from($this);
        }

        $lastFragment = $this->getLastFragment();
        $dayBoundary = self::getDayBoundary($lastFragment->range->start);
        assert($lastFragment->range->start < $dayOfEnd);

        // 終了日時が日跨ぎの区切り位置以前である場合は分割しない
        if ($end <= $dayBoundary) {
            return Seq::from($this);
        }

        return Seq::from(
            $this->copy([
                'range' => $this->range->copy(['end' => $dayBoundary]),
                'fragments' => Seq::from(...$this->generateFragmentsOf1stDay($dayBoundary)),
            ]),
            self::create([
                'userId' => $this->userId,
                'category' => $this->category,
                'isEmergency' => $this->isEmergency,
                'isFirst' => false,
                'isBehavioralDisorderSupportCooperation' => $this->isBehavioralDisorderSupportCooperation,
                'providedOn' => $dayBoundary->startOfDay(),
                'range' => $this->range->copy(['start' => $dayBoundary]),
                'fragments' => Seq::from(...$this->generateFragmentsOf2ndDay($dayBoundary)),
            ]),
        );
    }

    /**
     * 最後に始まる要素（Fragment）を取得する.
     *
     * @throws \Exception
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment
     */
    private function getLastFragment(): Fragment
    {
        return $this->fragments->maxBy(fn (Fragment $x): Carbon => $x->range->start);
    }

    /**
     * 1日目の要素を生成する.
     *
     * @param \Domain\Common\Carbon $dayBoundary
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment[]|iterable
     */
    private function generateFragmentsOf1stDay(Carbon $dayBoundary): iterable
    {
        foreach ($this->fragments as $fragment) {
            if ($fragment->range->end <= $dayBoundary) {
                yield $fragment;
            } else {
                $range = $fragment->range->copy(['end' => $dayBoundary]);
                yield $fragment->copy(['range' => $range]);
            }
        }
    }

    /**
     * 2日目の要素を生成する.
     *
     * @param \Domain\Common\Carbon $dayBoundary
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment[]|iterable
     */
    private function generateFragmentsOf2ndDay(Carbon $dayBoundary): iterable
    {
        $fragments = $this->fragments->filter(fn (Fragment $x): bool => $x->range->end > $dayBoundary);
        foreach ($fragments as $fragment) {
            $range = $fragment->range->copy(['start' => $dayBoundary]);
            yield $fragment->copy(['range' => $range]);
        }
    }
}

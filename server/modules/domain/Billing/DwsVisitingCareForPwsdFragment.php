<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護）要素.
 *
 * @property-read bool $isHospitalized 入院
 * @property-read bool $isLongHospitalized 入院（長期）
 * @property-read bool $isCoaching 同行（熟練同行・同行支援）
 * @property-read bool $isMoving 移動加算
 * @property-read bool $isSecondary 2人目
 * @property-read int $movingDurationMinutes 移動加算対象時間数（分）
 * @property-read \Domain\Common\CarbonRange $range 時間範囲
 * @property-read int $headcount 人数
 */
final class DwsVisitingCareForPwsdFragment extends Model
{
    /**
     * 合成可能であることを保証する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment $that
     * @return bool
     */
    public function isComposable(self $that): bool
    {
        return
            // 自分自身とは合成できない
            $this !== $that
            // 2人目の要素は合成できない
            && !$this->isSecondary
            && !$that->isSecondary
            // 各種フラグが異なる要素同士は合成できない
            && $this->isMoving === $that->isMoving
            && $this->isHospitalized === $that->isHospitalized
            && $this->isLongHospitalized === $that->isLongHospitalized
            && $this->isCoaching === $that->isCoaching
            // 時間範囲が重複（または連続）していない要素同士は合成できない
            && $this->range->isOverlapping($that->range)
            // 合計人数が2人を超える場合は合成できない（ただし時間帯が重複ではなく連続である場合を除く）
            && ($this->range->isConsecutive($that->range) || $this->headcount + $that->headcount <= 2);
    }

    /**
     * 移動加算対象時間数と時間範囲が一致するかどうかを判定する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment $that
     * @return bool
     */
    public function hasSameDuration(self $that): bool
    {
        return $this->range->equals($that->range)
            && $this->movingDurationMinutes === $that->movingDurationMinutes;
    }

    /**
     * サービス提供時間数を分単位で取得する.
     *
     * @return int
     */
    public function getDurationMinutes(): int
    {
        $range = $this->range;
        return $range->start->diffInMinutes($range->end);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'isHospitalized',
            'isLongHospitalized',
            'isCoaching',
            'isMoving',
            'isSecondary',
            'movingDurationMinutes',
            'range',
            'headcount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'isHospitalized' => true,
            'isLongHospitalized' => true,
            'isCoaching' => true,
            'isMoving' => true,
            'isSecondary' => true,
            'movingDurationMinutes' => true,
            'range' => true,
            'headcount' => true,
        ];
    }
}

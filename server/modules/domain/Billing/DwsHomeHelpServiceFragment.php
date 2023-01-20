<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）要素.
 *
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $providerType 提供者区分
 * @property-read bool $isSecondary 2人目
 * @property-read \Domain\Common\CarbonRange $range 時間範囲
 * @property-read int $headcount 人数
 */
class DwsHomeHelpServiceFragment extends Model
{
    /**
     * 合成可能であることを保証する.
     *
     * @param self $that
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
            // 提供者区分が異なる要素同士は合成できない
            && $this->providerType === $that->providerType
            // 時間範囲が重複（または連続）していない要素同士は合成できない
            && $this->range->isOverlapping($that->range)
            // 合計人数が2人を超える場合は合成できない（ただし時間帯が重複ではなく連続である場合を除く）
            && ($this->range->isConsecutive($that->range) || $this->headcount + $that->headcount <= 2);
    }

    /**
     * 時間範囲が一致するかどうかを判定する.
     *
     * @param self $that
     * @return bool
     */
    public function hasSameDuration(self $that): bool
    {
        return $this->range->equals($that->range);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'providerType',
            'isSecondary',
            'range',
            'headcount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'providerType' => true,
            'isSecondary' => true,
            'range' => true,
            'headcount' => true,
        ];
    }
}

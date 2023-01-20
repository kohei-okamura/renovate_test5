<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護） 実装.
 */
final class DwsVisitingCareForPwsdChunkImpl extends Entity implements DwsVisitingCareForPwsdChunk
{
    use DwsVisitingCareForPwsdChunkComposeMixin;
    use DwsVisitingCareForPwsdChunkGetDurationsMixin;
    use DwsVisitingCareForPwsdChunkSplitMixin;

    /** {@inheritdoc} */
    public function isEffective(): bool
    {
        // 2人目を除いた要素の合計時間数が1日の最低時間数（概ね30分）以上の場合に有効（算定可能）.
        $duration = $this->fragments
            ->filterNot(fn (DwsVisitingCareForPwsdFragment $x): bool => $x->isSecondary)
            ->map(fn (DwsVisitingCareForPwsdFragment $x): int => $x->getDurationMinutes())
            ->sum();
        return $duration >= self::MIN_DURATION_MINUTES_OF_DAY;
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'category',
            'isEmergency',
            'isFirst',
            'isBehavioralDisorderSupportCooperation',
            'providedOn',
            'range',
            'fragments',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'userId' => true,
            'category' => true,
            'isEmergency' => true,
            'isFirst' => true,
            'isBehavioralDisorderSupportCooperation' => true,
            'providedOn' => true,
            'range' => true,
            'fragments' => true,
        ];
    }
}

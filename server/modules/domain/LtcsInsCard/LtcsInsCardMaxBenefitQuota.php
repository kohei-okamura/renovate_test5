<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsInsCard;

use Domain\Model;

/**
 * 介護保険被保険者証：種類支給限度基準額.
 *
 * @property \Domain\LtcsInsCard\LtcsInsCardServiceType $ltcsInsCardServiceType サービス内容
 * @property int $maxBenefitQuota 種類支給限度基準額
 */
final class LtcsInsCardMaxBenefitQuota extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'ltcsInsCardServiceType',
            'maxBenefitQuota',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'ltcsInsCardServiceType' => true,
            'maxBenefitQuota' => true,
        ];
    }
}

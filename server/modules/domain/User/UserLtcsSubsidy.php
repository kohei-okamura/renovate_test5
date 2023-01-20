<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Entity;

/**
 * 利用者：公費情報.
 *
 * @property-read int $userId 利用者ID
 * @property-read \Domain\Common\CarbonRange $period 適用期間
 * @property-read \Domain\Common\DefrayerCategory $defrayerCategory 公費制度種別
 * @property-read string $defrayerNumber 負担者番号
 * @property-read string $recipientNumber 受給者番号
 * @property-read int $benefitRate 給付率
 * @property-read int $copay 本人負担額
 * @property-read int $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class UserLtcsSubsidy extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'period',
            'defrayerCategory',
            'defrayerNumber',
            'recipientNumber',
            'benefitRate',
            'copay',
            'isEnabled',
            'version',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'userId' => true,
            'period' => true,
            'defrayerCategory' => true,
            'defrayerNumber' => true,
            'recipientNumber' => true,
            'benefitRate' => true,
            'copay' => true,
            'isEnabled' => true,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

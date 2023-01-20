<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Entity;

/**
 * 出勤確認.
 *
 * @property-read int $staffId スタッフID
 * @property-read array|int[] $shiftIds 勤務シフトID
 * @property-read string $token トークン
 * @property-read \Domain\Common\Carbon $expiredAt 有効期限
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class Calling extends Entity
{
    public const FIRST_TARGET_MINUTES = 120;
    public const SECOND_TARGET_MINUTES = 90;
    public const THIRD_TARGET_MINUTES = 70;
    public const FOURTH_TARGET_MINUTES = 60;
    public const TARGET_RANGE = 5;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'staffId',
            'shiftIds',
            'token',
            'expiredAt',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'staffId' => true,
            'shiftIds' => true,
            'token' => true,
            'expiredAt' => true,
            'createdAt' => true,
        ];
    }
}

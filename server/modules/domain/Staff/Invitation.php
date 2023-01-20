<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Entity;

/**
 * 招待.
 *
 * @property-read null|int $staffId スタッフID
 * @property-read string $email メールアドレス
 * @property-read string $token トークン
 * @property-read int[] $roleIds ロールID
 * @property-read int[] $officeIds 事業所ID
 * @property-read int[] $officeGroupIds 事業所グループID
 * @property-read \Domain\Common\Carbon $expiredAt 有効期限
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class Invitation extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'staffId',
            'email',
            'token',
            'roleIds',
            'officeIds',
            'officeGroupIds',
            'expiredAt',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'staffId' => false,
            'email' => true,
            'token' => true,
            'roleIds' => true,
            'officeIds' => true,
            'officeGroupIds' => true,
            'expiredAt' => true,
            'createdAt' => true,
        ];
    }
}

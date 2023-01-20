<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Entity;

/**
 * スタッフ：メールアドレス確認.
 *
 * @property-read int $staffId スタッフID
 * @property-read \Domain\Common\StructuredName $name スタッフ名
 * @property-read string $email メールアドレス
 * @property-read string $token トークン
 * @property-read \Domain\Common\Carbon $expiredAt 有効期限
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class StaffEmailVerification extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'staffId',
            'name',
            'email',
            'token',
            'expiredAt',
            'createdAt',
        ];
    }
}

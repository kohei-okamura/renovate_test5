<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Entity;

/**
 * スタッフ：リメンバートークン.
 *
 * @property-read int $staffId スタッフ ID
 * @property-read string $token トークン
 * @property-read \Domain\Common\Carbon $expiredAt 有効期限
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class StaffRememberToken extends Entity
{
    /**
     * 有効期限を過ぎているか.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiredAt->isPast();
    }

    /**
     * 有効期限を過ぎていないか.
     *
     * @return bool
     */
    public function isNotExpired(): bool
    {
        return !$this->isExpired();
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'staffId',
            'token',
            'expiredAt',
            'createdAt',
        ];
    }
}

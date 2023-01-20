<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Password;

/**
 * {@link \Domain\Common\Password} Holder.
 *
 * @property \Domain\Common\Password $sex 性別
 * @method static \Illuminate\Database\Eloquent\Builder|static wherePassword($value)
 * @mixin \Eloquent
 */
trait PasswordHolder
{
    /**
     * Get mutator for password attribute.
     *
     * @return \Domain\Common\Password
     * @noinspection PhpUnused
     */
    protected function getPasswordAttribute(): Password
    {
        return Password::fromHashString($this->attributes['password_hash']);
    }

    /**
     * Set mutator for password attribute.
     *
     * @param \Domain\Common\Password $password
     * @return void
     * @noinspection PhpUnused
     */
    protected function setPasswordAttribute(Password $password): void
    {
        $this->attributes['password_hash'] = $password->hashString();
    }
}

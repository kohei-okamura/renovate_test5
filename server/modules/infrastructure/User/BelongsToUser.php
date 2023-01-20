<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsTo: {@link \Infrastructure\User\User}.
 *
 * @property int $user_id 利用者ID
 * @property-read \Infrastructure\User\User $user 利用者
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUserId($value)
 * @mixin \Eloquent
 */
trait BelongsToUser
{
    /**
     * BelongsTo: {@link \Infrastructure\User\User}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

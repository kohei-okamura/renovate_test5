<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsSubsidyFactor;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\User\UserDwsSubsidyFactor} 相互変換処理.
 */
final class CastsUserDwsSubsidyFactor implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): UserDwsSubsidyFactor
    {
        return UserDwsSubsidyFactor::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof UserDwsSubsidyFactor);
        return $value->value();
    }
}

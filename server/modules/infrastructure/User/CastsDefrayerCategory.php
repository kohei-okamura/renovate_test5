<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\Common\DefrayerCategory;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Billing\DefrayerCategory} 相互変換処理.
 */
final class CastsDefrayerCategory implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): ?DefrayerCategory
    {
        return $value === null
            ? null
            : DefrayerCategory::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }
        assert($value instanceof DefrayerCategory);
        return $value->value();
    }
}

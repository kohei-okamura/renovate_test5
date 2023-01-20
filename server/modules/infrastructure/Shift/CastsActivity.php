<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Activity;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Shift\Activity} 相互変換処理.
 */
final class CastsActivity implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): Activity
    {
        return Activity::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof Activity);
        return $value->value();
    }
}

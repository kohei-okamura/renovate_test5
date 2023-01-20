<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用「年月日」⇔「整数」相互変換処理.
 *
 * SQLite には DATE 型がないため年月日を UNIX タイムスタンプとして保管する必要がある.
 */
final class CastsDateAsInt implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): Carbon
    {
        return Carbon::createFromTimestamp($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof Carbon);
        return $value->startOfDay()->unix();
    }
}

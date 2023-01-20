<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Domain\Permission\Permission;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Permission\Permission} 相互変換処理.
 */
final class CastsPermission implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): Permission
    {
        return Permission::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): string
    {
        assert($value instanceof Permission);
        return $value->value();
    }
}

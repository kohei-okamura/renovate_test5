<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Office\LtcsBaseIncreaseSupportAddition} 相互変換処理.
 */
final class CastsLtcsBaseIncreaseSupportAddition implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): LtcsBaseIncreaseSupportAddition
    {
        return LtcsBaseIncreaseSupportAddition::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof LtcsBaseIncreaseSupportAddition);
        return $value->value();
    }
}

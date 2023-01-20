<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsServiceDivisionCode;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Billing\LtcsServiceDivisionCode} 相互変換処理.
 */
final class CastsLtcsServiceDivisionCode implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): LtcsServiceDivisionCode
    {
        return LtcsServiceDivisionCode::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): string
    {
        assert($value instanceof LtcsServiceDivisionCode);
        return $value->value();
    }
}

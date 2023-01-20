<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCardStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\LtcsInsCard\LtcsInsCardStatus} 相互変換処理.
 */
final class CastsLtcsInsCardStatus implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): LtcsInsCardStatus
    {
        return LtcsInsCardStatus::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof LtcsInsCardStatus);
        return $value->value();
    }
}

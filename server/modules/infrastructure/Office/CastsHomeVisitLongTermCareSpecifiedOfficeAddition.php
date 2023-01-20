<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition} 相互変換処理.
 */
final class CastsHomeVisitLongTermCareSpecifiedOfficeAddition implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): HomeVisitLongTermCareSpecifiedOfficeAddition
    {
        return HomeVisitLongTermCareSpecifiedOfficeAddition::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof HomeVisitLongTermCareSpecifiedOfficeAddition);
        return $value->value();
    }
}

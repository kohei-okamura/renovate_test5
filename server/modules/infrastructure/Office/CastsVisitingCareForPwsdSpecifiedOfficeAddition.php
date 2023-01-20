<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition} 相互変換処理.
 */
final class CastsVisitingCareForPwsdSpecifiedOfficeAddition implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): VisitingCareForPwsdSpecifiedOfficeAddition
    {
        return VisitingCareForPwsdSpecifiedOfficeAddition::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof VisitingCareForPwsdSpecifiedOfficeAddition);
        return $value->value();
    }
}

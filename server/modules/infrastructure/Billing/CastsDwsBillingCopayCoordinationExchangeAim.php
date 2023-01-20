<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Billing\DwsBillingCopayCoordinationExchangeAim} 相互変換処理.
 */
final class CastsDwsBillingCopayCoordinationExchangeAim implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, mixed $value, array $attributes): DwsBillingCopayCoordinationExchangeAim
    {
        return DwsBillingCopayCoordinationExchangeAim::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof DwsBillingCopayCoordinationExchangeAim);
        return $value->value();
    }
}

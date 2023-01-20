<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Billing\DwsBillingStatementCopayCoordinationStatus} 相互変換処理.
 */
final class CastsDwsBillingStatementCopayCoordinationStatus implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): DwsBillingStatementCopayCoordinationStatus
    {
        return DwsBillingStatementCopayCoordinationStatus::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof DwsBillingStatementCopayCoordinationStatus);
        return $value->value();
    }
}

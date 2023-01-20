<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingServiceReportFormat;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Billing\DwsBillingServiceReportFormat} 相互変換処理.
 */
final class CastsDwsBillingServiceReportFormat implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): DwsBillingServiceReportFormat
    {
        return DwsBillingServiceReportFormat::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): string
    {
        assert($value instanceof DwsBillingServiceReportFormat);
        return $value->value();
    }
}

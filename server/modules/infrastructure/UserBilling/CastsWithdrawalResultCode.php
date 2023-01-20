<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\UserBilling\WithdrawalResultCode;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\UserBilling\WithdrawalResultCode} 相互変換処理.
 */
final class CastsWithdrawalResultCode implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): WithdrawalResultCode
    {
        return WithdrawalResultCode::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof WithdrawalResultCode);
        return $value->value();
    }
}

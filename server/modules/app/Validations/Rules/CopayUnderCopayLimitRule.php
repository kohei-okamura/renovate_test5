<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementFinder;
use Illuminate\Support\Arr;

/**
 * 利用者負担額が利用者負担上限月額より低い値であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait CopayUnderCopayLimitRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateCopayUnderCopayLimit(string $attribute, mixed $value, array $parameters): bool
    {
        $bundleId = (int)Arr::get($this->data, 'dwsBillingBundleId', 0);
        $userId = (int)Arr::get($this->data, $parameters[0]);

        /** @var \Domain\Billing\DwsBillingStatementFinder $finder */
        $finder = app(DwsBillingStatementFinder::class);
        return $finder
            ->find(
                ['dwsBillingBundleId' => $bundleId, 'userId' => $userId],
                ['all' => true, 'sortBy' => 'id']
            )
            ->list
            ->headOption()
            ->map(fn (DwsBillingStatement $x): bool => $x->copayLimit >= $value)
            ->getOrElseValue(true);
    }
}

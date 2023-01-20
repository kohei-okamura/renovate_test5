<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatus;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\Billing\LookupDwsBillingUseCase;

/**
 * 障害福祉サービスがコピー可能であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsBillingCanCopyRule
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
    protected function validateDwsBillingCanCopy(string $attribute, mixed $value, array $parameters): bool
    {
        $id = (int)Arr::get($this->data, 'id', -1);

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupDwsBillingUseCase::class);

        return $lookupBillingUseCase->handle($this->context, Permission::createBillings(), $id)
            ->headOption()
            ->map(fn (DwsBilling $x): bool => $x->status === DwsBillingStatus::fixed())
            ->getOrElseValue(false);
    }
}

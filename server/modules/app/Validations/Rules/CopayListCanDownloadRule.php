<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBillingStatement;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\Billing\LookupDwsBillingUseCase;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;

/**
 * 利用者負担額一覧表がダウンロード可能であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait CopayListCanDownloadRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateCopayListCanDownload(string $attribute, $value, array $parameters): bool
    {
        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $ids = $value;
        if (!is_array($ids)) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupDwsBillingUseCase */
        $lookupDwsBillingUseCase = app(LookupDwsBillingUseCase::class);
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $lookupDwsBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        if ($billing === null) {
            return true;
        }

        /** @var \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $simpleLookupDwsBillingStatementUseCase */
        $simpleLookupDwsBillingStatementUseCase = app(SimpleLookupDwsBillingStatementUseCase::class);
        /** @var \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $simpleLookupDwsBillingStatementUseCase
            ->handle($this->context, Permission::updateBillings(), ...$ids)
            ->filter(fn (DwsBillingStatement $x): bool => $x->dwsBillingId === $billingId);

        return $statements->forAll(function (DwsBillingStatement $x) use ($billing): bool {
            return $x->copayCoordinationStatus->isSelfOffice(
                fn (): bool => $x->copayCoordination === null || $x->copayCoordination->office->officeId !== $billing->office->officeId
            );
        });
    }
}

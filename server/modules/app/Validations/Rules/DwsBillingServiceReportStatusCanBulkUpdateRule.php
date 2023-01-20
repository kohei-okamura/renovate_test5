<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatus;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\Billing\LookupDwsBillingUseCase;
use UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase;

/**
 * 指定されたサービス提供実績記録票の状態が更新可能な値であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsBillingServiceReportStatusCanBulkUpdateRule
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
    protected function validateDwsBillingServiceReportStatusCanBulkUpdate(string $attribute, $value, array $parameters): bool
    {
        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $ids = $value;
        if (!is_array($ids)) {
            return true;
        }

        $statusValue = Arr::get($this->data, $parameters[0]);
        if (!DwsBillingStatus::isValid((int)$statusValue)) {
            return true;
        }

        $updatedStatus = DwsBillingStatus::from($statusValue);
        // 「未確定」 or 「確定済」 以外には更新できない
        if ($updatedStatus !== DwsBillingStatus::ready() && $updatedStatus !== DwsBillingStatus::fixed()) {
            return false;
        }

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupDwsBillingUseCase::class);
        // 請求が存在しない or 請求の状態が「確定済」の場合は更新不可
        $billingExistsAndIsNotFixed = $lookupBillingUseCase
            ->handle($this->context, Permission::updateBillings(), $billingId)
            ->exists(fn (DwsBilling $x): bool => $x->status !== DwsBillingStatus::fixed());
        if (!$billingExistsAndIsNotFixed) {
            return false;
        }

        /** @var \UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase $simpleLookupDwsBillingServiceReportUseCase */
        $simpleLookupDwsBillingServiceReportUseCase = app(SimpleLookupDwsBillingServiceReportUseCase::class);
        /** @var \Domain\Billing\DwsBillingServiceReport[]|\ScalikePHP\Seq $entities */
        $entities = $simpleLookupDwsBillingServiceReportUseCase
            ->handle($this->context, Permission::updateBillings(), ...$ids)
            ->filter(fn (DwsBillingServiceReport $x): bool => $x->dwsBillingId === $billingId);
        // 状態一括更新APIでは、サービス提供実績記録票が存在しない ID が含まれている場合はエラーにする
        if ($entities->size() !== count($ids)) {
            return false;
        }

        // 未確定 => 確定済 or 確定済 => 未確定 以外の変更は不可（それ以外の状態は事前にエラーにしているので考慮不要）
        $expectedStatus = $updatedStatus === DwsBillingStatus::ready() ? DwsBillingStatus::fixed() : DwsBillingStatus::ready();
        return $entities
            ->forAll(fn (DwsBillingServiceReport $x): bool => $x->status === $expectedStatus);
    }
}

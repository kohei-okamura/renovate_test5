<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBillingStatus;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\Billing\LookupDwsBillingServiceReportUseCase;
use UseCase\Billing\LookupDwsBillingUseCase;

/**
 * 指定されたサービス提供実績記録票の状態が更新可能な値であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsBillingServiceReportStatusCanUpdateRule
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
    protected function validateDwsBillingServiceReportStatusCanUpdate(string $attribute, $value, array $parameters): bool
    {
        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'billingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $reportId = (int)Arr::get($this->data, 'id', 0);
        if ($reportId === 0) {
            return true;
        }

        if (!DwsBillingStatus::isValid((int)$value)) {
            return true;
        }

        $updatedStatus = DwsBillingStatus::from($value);
        // 「未確定」 or 「確定済」 以外には更新できない
        if ($updatedStatus !== DwsBillingStatus::ready() && $updatedStatus !== DwsBillingStatus::fixed()) {
            return false;
        }

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupDwsBillingUseCase::class);
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        if ($billing === null) {
            return true;
        }
        // 請求の状態が「確定済」または「無効」の場合は更新不可
        if ($billing->status === DwsBillingStatus::fixed() || $billing->status === DwsBillingStatus::disabled()) {
            return false;
        }

        /** @var \UseCase\Billing\LookupDwsBillingServiceReportUseCase $lookupDwsBillingServiceReportUseCase */
        $lookupDwsBillingServiceReportUseCase = app(LookupDwsBillingServiceReportUseCase::class);
        /** @var \Domain\Billing\DwsBillingServiceReport $currentEntity */
        $currentEntity = $lookupDwsBillingServiceReportUseCase
            ->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, $reportId)
            ->headOption()
            ->orNull();
        // このバリデーションでは存在チェックはしない（別でやる）
        if ($currentEntity === null) {
            return true;
        }

        // 未確定 => 確定済 or 確定済 => 未確定 以外の変更は不可
        return ($currentEntity->status === DwsBillingStatus::ready() && $updatedStatus === DwsBillingStatus::fixed())
            || ($currentEntity->status === DwsBillingStatus::fixed() && $updatedStatus === DwsBillingStatus::ready());
    }
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatus;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\Billing\LookupLtcsBillingBundleUseCase;
use UseCase\Billing\LookupLtcsBillingUseCase;
use UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase;
use UseCase\ProvisionReport\FindLtcsProvisionReportUseCase;

/**
 * 指定された介護保険サービス：明細書がリフレッシュ可能であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait LtcsBillingStatementCanRefreshRule
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
    protected function validateLtcsBillingStatementCanRefresh(string $attribute, $value, array $parameters): bool
    {
        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $ids = $value;
        if (!is_array($ids)) {
            return true;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupLtcsBillingUseCase::class);
        /** @var \Domain\Billing\LtcsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        // 請求が存在しない or 請求の状態が「確定済」の場合は 400 にしたいのでエラーとする
        if ($billing === null || $billing->status === LtcsBillingStatus::fixed()) {
            return false;
        }

        /** @var \UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase $lookupStatementUseCase */
        $lookupStatementUseCase = app(SimpleLookupLtcsBillingStatementUseCase::class);
        $statements = $lookupStatementUseCase
            ->handle($this->context, Permission::updateBillings(), ...$ids)
            ->filter(fn (LtcsBillingStatement $x): bool => $x->billingId === $billingId);
        $statementsSize = $statements->size();
        // 指定した ID の中に紐づく明細書が存在しない、もしくは「確定済」の ID が含まれている場合は 400 にしたいのでエラーとする
        $hasInvalidStatementState = $statementsSize !== count($ids)
            || $statements->exists(fn (LtcsBillingStatement $x): bool => $x->status === LtcsBillingStatus::fixed());
        if ($hasInvalidStatementState) {
            return false;
        }

        $bundleIds = $statements->map(fn (LtcsBillingStatement $x) => $x->bundleId)->distinct();
        // 請求単位 ID が 1 つじゃない場合は何かおかしいのでエラー
        if ($bundleIds->size() !== 1) {
            return false;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingBundleUseCase $lookupLtcsBillingBundleUseCase */
        $lookupLtcsBillingBundleUseCase = app(LookupLtcsBillingBundleUseCase::class);
        /** @var \Domain\Billing\LtcsBillingBundle $bundle */
        $bundle = $lookupLtcsBillingBundleUseCase
            ->handle($this->context, Permission::updateBillings(), $billingId, ...$bundleIds->toArray())
            ->headOption()
            ->orNull();
        // 請求単位が存在しない場合は 400 にしたいのでエラーとする
        if ($bundle === null) {
            return false;
        }

        /** @var \UseCase\ProvisionReport\FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase */
        $findLtcsProvisionReportUseCase = app(FindLtcsProvisionReportUseCase::class);
        $provisionReports = $findLtcsProvisionReportUseCase
            ->handle(
                $this->context,
                Permission::updateBillings(),
                [
                    'officeId' => $billing->office->officeId,
                    'providedIn' => $bundle->providedIn,
                    'userIds' => $statements->map(fn (LtcsBillingStatement $x) => $x->user->userId)->toArray(),
                ],
                ['all' => true],
            )
            ->list;
        // 明細書と予実の件数が異なる場合は何かおかしいのでエラー
        if ($statementsSize !== $provisionReports->size()) {
            return false;
        }

        // 対象の事業所・サービス提供年月・利用者に紐づくすべての予実が以下の条件を満たさない場合はエラー
        // - 状態が「確定済」
        // - 実績に介護保険サービスが 1 件以上登録されている
        return $provisionReports->forAll(function (LtcsProvisionReport $x) {
            return $x->status === LtcsProvisionReportStatus::fixed()
                && Seq::fromArray($x->entries)
                    ->filter(function (LtcsProvisionReportEntry $entry) {
                        return $entry->category !== LtcsProjectServiceCategory::ownExpense()
                            && !empty($entry->results);
                    })->size() >= 1;
        });
    }
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\DwsCertification\DwsCertification;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\Billing\LookupDwsBillingBundleUseCase;
use UseCase\Billing\LookupDwsBillingUseCase;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\ProvisionReport\FindDwsProvisionReportUseCase;

/**
 * 指定された障害福祉サービス：明細書がリフレッシュ可能であることを検証する（予実）.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsBillingStatementCanRefreshRule
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
    protected function validateDwsBillingStatementCanRefresh(string $attribute, $value, array $parameters): bool
    {
        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $ids = $value;
        if (!is_array($ids)) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupDwsBillingUseCase::class);
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        // 請求が存在しない or 請求の状態が「確定済」の場合は 400 にしたいのでエラーとする
        if ($billing === null || $billing->status === DwsBillingStatus::fixed()) {
            return false;
        }

        /** @var \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupStatementUseCase */
        $lookupStatementUseCase = app(SimpleLookupDwsBillingStatementUseCase::class);
        $statements = $lookupStatementUseCase
            ->handle($this->context, Permission::updateBillings(), ...$ids)
            ->filter(fn (DwsBillingStatement $x): bool => $x->dwsBillingId === $billingId);
        $statementsSize = $statements->size();
        // 指定した ID の中に紐づく明細書が存在しない場合は 400 にしたいのでエラーとする
        if ($statementsSize !== count($ids)) {
            return false;
        }
        $userIds = $statements->map(fn (DwsBillingStatement $x) => $x->user->userId);

        $bundleIds = $statements->map(fn (DwsBillingStatement $x) => $x->dwsBillingBundleId)->distinct();

        /** @var \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupBundleUseCase */
        $lookupBundleUseCase = app(LookupDwsBillingBundleUseCase::class);
        /** @var \Domain\Billing\DwsBillingBundle $bundle */
        $bundle = $lookupBundleUseCase
            ->handle($this->context, Permission::updateBillings(), $billingId, ...$bundleIds->toArray())
            ->headOption()
            ->orNull();
        // 請求単位が存在しない場合は 400 にしたいのでエラーとする
        if ($bundle === null) {
            return false;
        }

        /** @var \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase */
        $identifyDwsCertificationUseCase = app(IdentifyDwsCertificationUseCase::class);
        $dwsCertifications = $userIds->flatMap(
            fn (int $userId) => $identifyDwsCertificationUseCase
                ->handle($this->context, $userId, $bundle->providedIn)
        );
        // 受給者証が見つからない場合はエラー
        if ($dwsCertifications->size() !== $userIds->size()) {
            return false;
        }

        // 上限管理を自事業所が行っている利用者 ID の一覧
        $selfCoordinationUserIds = $dwsCertifications
            ->filter(fn (DwsCertification $x): bool => $x->copayCoordination->officeId === $billing->office->officeId)
            ->map(fn (DwsCertification $x): int => $x->userId);

        /** @var \UseCase\ProvisionReport\FindDwsProvisionReportUseCase $findProvisionReportUseCase */
        $findProvisionReportUseCase = app(FindDwsProvisionReportUseCase::class);
        $provisionReports = $findProvisionReportUseCase
            ->handle(
                $this->context,
                Permission::updateBillings(),
                [
                    'officeId' => $billing->office->officeId,
                    'providedIn' => $bundle->providedIn,
                    'userIds' => $userIds->toArray(),
                ],
                ['all' => true],
            )
            ->list;

        // 予実がない場合はエラー
        // ※ただし、上限管理を自事業所が行っている場合は利用者負担上限額管理加算を算定するため、予実自体がなくてもよい
        $provisionReportExists = $statements->forAll(
            fn (DwsBillingStatement $x): bool => $provisionReports->exists(fn (DwsProvisionReport $y): bool => $y->userId === $x->user->userId)
                || $selfCoordinationUserIds->contains($x->user->userId)
        );
        if (!$provisionReportExists) {
            return false;
        }

        // 対象の事業所・サービス提供年月・利用者に紐づくすべての予実が以下の条件を満たさない場合はエラー
        // - 状態が「確定済」
        // - 実績に障害福祉サービスが1件以上登録されている
        //   ※ただし、上限管理を自事業所が行っている場合は利用者負担上限額管理加算を算定するため、障害福祉サービスが0件でもエラーとしない
        $isFixed = fn (DwsProvisionReport $x): bool => $x->status === DwsProvisionReportStatus::fixed();
        $dwsResultExists = fn (DwsProvisionReport $x): bool => Seq::fromArray($x->results)
            ->exists(fn (DwsProvisionReportItem $item): bool => !$item->isOwnExpense());
        $isSelfCoordination = fn (DwsProvisionReport $x): bool => $selfCoordinationUserIds->contains($x->userId);
        return $provisionReports->forAll(
            fn (DwsProvisionReport $x): bool => $isFixed($x)
                && ($dwsResultExists($x) || $isSelfCoordination($x))
        );
    }
}

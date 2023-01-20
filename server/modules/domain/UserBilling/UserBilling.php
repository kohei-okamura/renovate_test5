<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Entity;
use ScalikePHP\Seq;

/**
 * 利用者請求.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read int $userId 利用者ID
 * @property-read int $officeId 事業所ID
 * @property-read \Domain\UserBilling\UserBillingUser $user 利用者
 * @property-read \Domain\UserBilling\UserBillingOffice $office 事業所
 * @property-read null|\Domain\UserBilling\UserBillingDwsItem $dwsItem 障害福祉サービス明細
 * @property-read null|\Domain\UserBilling\UserBillingLtcsItem $ltcsItem 介護保険サービス明細
 * @property-read null|\Domain\UserBilling\UserBillingOtherItem[] $otherItems その他サービス明細
 * @property-read \Domain\UserBilling\UserBillingResult $result 請求結果
 * @property-read int $totalAmount 合計金額
 * @property-read int $carriedOverAmount 繰越金額
 * @property-read null|\Domain\UserBilling\WithdrawalResultCode $withdrawalResultCode 振替結果コード
 * @property-read \Domain\Common\Carbon $providedIn サービス提供年月
 * @property-read null|\Domain\Common\Carbon $issuedOn 発行日
 * @property-read null|\Domain\Common\Carbon $depositedAt 入金日時
 * @property-read null|\Domain\Common\Carbon $transactedAt 処理日時
 * @property-read null|\Domain\Common\Carbon $deductedOn 口座振替日
 * @property-read \Domain\Common\Carbon $dueDate お支払期限日
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class UserBilling extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'userId',
            'officeId',
            'user',
            'office',
            'dwsItem',
            'ltcsItem',
            'otherItems',
            'result',
            'carriedOverAmount',
            'withdrawalResultCode',
            'providedIn',
            'issuedOn',
            'depositedAt',
            'transactedAt',
            'deductedOn',
            'dueDate',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'organizationId' => false,
            'userId' => true,
            'officeId' => true,
            'user' => true,
            'office' => true,
            'dwsItem' => true,
            'ltcsItem' => true,
            'otherItems' => true,
            'result' => true,
            'totalAmount' => true,
            'carriedOverAmount' => true,
            'withdrawalResultCode' => true,
            'providedIn' => true,
            'issuedOn' => true,
            'depositedAt' => true,
            'transactedAt' => true,
            'deductedOn' => true,
            'dueDate' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }

    /** {@inheritdoc} */
    protected function computedAttrs(array $values): array
    {
        return [
            ...parent::computedAttrs($values),
            'totalAmount' => $this->calculateAmount($values),
        ];
    }

    /**
     * 請求金額を計算する.
     *
     * @param array $values
     * @return int
     */
    private function calculateAmount(array $values): int
    {
        $otherItems = Seq::fromArray(empty($values['otherItems']) ? null : $values['otherItems']);
        // 利用者請求の請求金額は税込になるので、totalAmount ではなく copayWithTax を使用する
        return (empty($values['dwsItem']) ? 0 : $values['dwsItem']->copayWithTax ?? 0)
            + (empty($values['ltcsItem']) ? 0 : $values['ltcsItem']->copayWithTax ?? 0)
            + (
                $otherItems->nonEmpty()
                ? $otherItems->map(fn (UserBillingOtherItem $x): int => $x->copayWithTax ?? 0)->sum()
                : 0
            )
            + $values['carriedOverAmount'];
    }
}

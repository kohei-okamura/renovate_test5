<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Model;

/**
 * 口座振替データ：明細.
 *
 * @property-read int[] $userBillingIds 利用者請求 ID
 * @property-read \Domain\UserBilling\ZenginDataRecord $zenginRecord 全銀データ
 */
class WithdrawalTransactionItem extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'userBillingIds',
            'zenginRecord',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'userBillingIds' => true,
            'zenginRecord' => true,
        ];
    }
}

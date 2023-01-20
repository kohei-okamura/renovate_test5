<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 障害福祉サービス：明細書：上限管理区分更新リクエスト.
 *
 * @property-read int $status
 */
class UpdateDwsBillingStatementCopayCoordinationStatusRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新する上限管理区分を取得する.
     *
     * @return \Domain\Billing\DwsBillingStatementCopayCoordinationStatus
     */
    public function payload(): DwsBillingStatementCopayCoordinationStatus
    {
        return DwsBillingStatementCopayCoordinationStatus::from($this->status);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'status' => [
                'required',
                'dws_billing_statement_copay_coordination_status',
                'dws_billing_statement_copay_coordination_status_can_update',
            ],
        ];
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsBillingStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 障害福祉サービス：請求 状態更新リクエスト.
 *
 * @property-read int $status 状態
 */
class UpdateDwsBillingStatusRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の状態を取得する.
     *
     * @return \Domain\Billing\DwsBillingStatus
     */
    public function payload(): DwsBillingStatus
    {
        return DwsBillingStatus::from($this->status);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'status' => ['required', 'dws_billing_status', 'dws_billing_status_can_update'],
        ];
    }
}

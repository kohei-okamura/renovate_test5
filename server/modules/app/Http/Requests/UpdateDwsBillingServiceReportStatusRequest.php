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
 * 障害福祉サービス：サービス提供実績記録票状態更新リクエスト.
 *
 * @property-read int $status
 */
class UpdateDwsBillingServiceReportStatusRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'status' => DwsBillingStatus::from($this->status),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'status' => [
                'required',
                'dws_billing_status',
                'dws_billing_service_report_status_can_update',
            ],
        ];
    }
}

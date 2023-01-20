<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsBillingStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 障害福祉サービス：サービス提供実績記録票状態一括更新リクエスト.
 *
 * @property-read array|int[] $ids
 * @property-read int $status
 */
class BulkUpdateDwsBillingServiceReportStatusRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新するステータスを取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'ids' => $this->ids,
            'status' => DwsBillingStatus::from($this->status),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => [
                'required',
                'array',
                'dws_billing_service_report_status_can_bulk_update:status',
            ],
            'status' => ['required', 'dws_billing_status'],
        ];
    }
}

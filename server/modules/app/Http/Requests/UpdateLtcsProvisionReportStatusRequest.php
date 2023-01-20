<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;

/**
 * 介護保険サービス：予実 状態更新リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 * @property-read int $status
 */
class UpdateLtcsProvisionReportStatusRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の状態を取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return ['status' => LtcsProvisionReportStatus::from($this->status)];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $officeId = Arr::get($input, 'officeId');
        return [
            'userId' => [
                'required',
                "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports(),
            ],
            'status' => ['required', 'ltcs_provision_report_status'],
        ];
    }
}

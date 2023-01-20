<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * 障害福祉サービス：予実 状態更新リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 * @property-read int $status
 */
class UpdateDwsProvisionReportStatusRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の状態を取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return ['status' => DwsProvisionReportStatus::from($this->status)];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $officeId = Arr::get($input, 'officeId');
        $providedIn = Arr::get($input, 'providedIn');
        $status = Arr::get($input, 'status');
        return [
            'userId' => [
                'required',
                "start_of_dws_contract_period_filled:{$officeId},{$providedIn}," . Permission::updateDwsProvisionReports(),
                "has_active_certification_grant:{$officeId},{$providedIn}," . Permission::updateDwsProvisionReports(),
                Rule::when(
                    $status === DwsProvisionReportStatus::fixed()->value(),
                    "has_active_certification_agreements:{$officeId},{$providedIn}," . Permission::updateDwsProvisionReports()
                ),
            ],
            'status' => ['required', 'dws_provision_report_status'],
        ];
    }
}

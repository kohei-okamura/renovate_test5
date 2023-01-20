<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書更新リクエスト.
 *
 * @property-read array|array[] $aggregates 集計
 */
class UpdateDwsBillingStatementRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return Seq::fromArray($this->aggregates)
            ->map(function (array $input): array {
                return [
                    'serviceDivisionCode' => DwsServiceDivisionCode::from($input['serviceDivisionCode']),
                    'managedCopay' => $input['managedCopay'],
                    'subtotalSubsidy' => $input['subtotalSubsidy'] ?? null,
                ];
            })
            ->toArray();
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'id' => ['dws_billing_statement_can_update'],
            'aggregates' => [
                'required',
                'array',
                'dws_service_division_code_exists:id,billingId,billingBundleId,' . Permission::updateBillings(),
            ],
            'aggregates.*.serviceDivisionCode' => ['required', 'dws_service_division_code'],
            'aggregates.*.managedCopay' => ['required', 'integer'],
            'aggregates.*.subtotalSubsidy' => ['nullable', 'integer'],
        ];
    }
}

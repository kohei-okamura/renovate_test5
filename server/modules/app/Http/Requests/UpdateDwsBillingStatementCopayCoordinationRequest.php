<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\CopayCoordinationResult;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 障害福祉サービス：明細書上限管理結果更新リクエスト.
 *
 * @property-read int $result 上限管理結果
 * @property-read int $amount 管理結果額
 */
class UpdateDwsBillingStatementCopayCoordinationRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'result' => CopayCoordinationResult::from($this->result),
            'amount' => $this->amount,
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'id' => ['dws_billing_statement_can_update', 'copay_coordination_result_can_update'],
            'result' => ['required', 'copay_coordination_result'],
            'amount' => ['required', 'integer', 'min:0'],
        ];
    }
}

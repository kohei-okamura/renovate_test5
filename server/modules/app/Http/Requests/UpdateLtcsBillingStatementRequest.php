<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\LtcsServiceDivisionCode;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書更新リクエスト.
 *
 * @property-read array $aggregates Aggregates
 */
class UpdateLtcsBillingStatementRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新する値を取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return Seq::fromArray($this->aggregates)
            ->map(fn (array $x): array => [
                'serviceDivisionCode' => LtcsServiceDivisionCode::from($x['serviceDivisionCode']),
                'plannedScore' => $x['plannedScore'],
            ])
            ->toArray();
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'aggregates' => ['required', 'array'],
            'aggregates.*.serviceDivisionCode' => ['required', 'ltcs_service_division_code'],
            'aggregates.*.plannedScore' => ['required', 'integer', 'min:0'],
        ];
    }
}

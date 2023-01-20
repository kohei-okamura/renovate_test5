<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\LtcsBillingStatus;
use Domain\Permission\Permission;

/**
 * 介護保険サービス：請求検索リクエスト.
 */
class FindLtcsBillingRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function carbonParams(): array
    {
        return [
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return ['statuses' => LtcsBillingStatus::class];
    }

    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [
            'start',
            'end',
            'statuses',
            'officeId',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            ...parent::rules($input),
            'start' => ['nullable', 'date_format:Y-m'],
            'end' => ['nullable', 'date_format:Y-m'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['required', 'ltcs_billing_status'],
            'officeId' => ['nullable', 'office_exists:' . Permission::listBillings()],
        ];
    }
}

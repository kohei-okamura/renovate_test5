<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsBillingStatus;
use Domain\Permission\Permission;

/**
 * 障害福祉サービス：請求検索リクエスト.
 */
class FindDwsBillingRequest extends FindRequest
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
        return ['statuses' => DwsBillingStatus::class];
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
            'statuses.*' => ['required', 'dws_billing_status'],
            'officeId' => ['nullable', 'office_exists:' . Permission::listBillings()],
        ];
    }
}

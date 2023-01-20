<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBillingUsedService\UserBillingUsedService;

/**
 * 利用者請求検索リクエスト.
 */
class FindUserBillingRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function boolParams(): array
    {
        return [
            'isTransacted',
            'isDeposited',
        ];
    }

    /** {@inheritdoc} */
    protected function carbonParams(): array
    {
        return [
            'providedIn',
            'issuedIn',
        ];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return [
            'usedService' => UserBillingUsedService::class,
            'result' => UserBillingResult::class,
            'paymentMethod' => PaymentMethod::class,
        ];
    }

    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [
            'providedIn',
            'issuedIn',
            'isTransacted',
            'isDeposited',
            'usedService',
            'result',
            'paymentMethod',
            'userId',
            'officeId',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            ...parent::rules($input),
            'providedIn' => ['nullable', 'date_format:Y-m'],
            'issuedIn' => ['nullable', 'date_format:Y-m'],
            'isTransacted' => ['boolean_ext'],
            'isDeposited' => ['boolean_ext'],
            'usedService' => ['nullable', 'user_billing_used_service'],
            'result' => ['nullable', 'user_billing_result'],
            'paymentMethod' => ['nullable', 'payment_method'],
            'userId' => ['nullable', 'user_exists:' . Permission::listUserBillings()],
            'officeId' => ['nullable', 'office_exists:' . Permission::listUserBillings()],
        ];
    }
}

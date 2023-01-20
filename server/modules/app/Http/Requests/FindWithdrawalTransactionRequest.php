<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

/**
 * 口座振替データ検索リクエスト.
 */
class FindWithdrawalTransactionRequest extends FindRequest
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
    protected function filterKeys(): array
    {
        return [
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            ...parent::rules($input),
            'start' => ['nullable', 'date'],
            'end' => ['bail', 'nullable', 'date', 'after_or_equal:start'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'start' => '作成日（開始）',
        ];
    }
}

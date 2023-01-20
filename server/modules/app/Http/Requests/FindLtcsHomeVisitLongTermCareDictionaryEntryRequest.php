<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCodeDictionary\Timeframe;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ検索リクエスト.
 */
class FindLtcsHomeVisitLongTermCareDictionaryEntryRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function carbonParams(): array
    {
        return ['isEffectiveOn'];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return [
            'category' => LtcsProjectServiceCategory::class,
            'timeframe' => Timeframe::class,
        ];
    }

    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [
            'officeId',
            'isEffectiveOn',
            'q',
            'timeframe',
            'category',
            'physicalMinutes',
            'houseworkMinutes',
            'headcount',
        ];
    }

    /** {@inheritdoc} */
    protected function integerParams(): array
    {
        return [
            'physicalMinutes',
            'houseworkMinutes',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'integer'], // DEV-4975 認可しないため、office_exists できない.
            'isEffectiveOn' => ['required', 'date'],
            'q' => ['nullable', 'string', 'size:6'],
            'timeframe' => ['nullable', 'timeframe'],
            'category' => ['nullable', 'ltcs_project_service_category'],
            'physicalMinutes' => ['nullable', 'integer', 'between:1,1440'],
            'houseworkMinutes' => ['nullable', 'integer', 'between:1,1440'],
            'headcount' => ['nullable', 'integer', 'between:1,2'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'q' => 'サービスコード',
        ];
    }
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Office\Purpose;

/**
 * 事業所検索リクエスト.
 *
 * @property-read null|string $q
 * @property-read null|int $prefecture
 * @property-read null|int $purpose
 * @property-read null|array $status
 */
class FindOfficeRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return ['q', 'prefecture', 'purpose', 'status'];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return ['purpose' => Purpose::class];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'prefecture' => ['nullable', 'prefecture'],
            'purpose' => ['nullable', 'purpose'],
        ] + parent::rules($input);
    }
}

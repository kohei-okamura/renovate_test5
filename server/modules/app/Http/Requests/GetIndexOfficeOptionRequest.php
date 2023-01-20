<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Option;

/**
 * 事業所選択肢一覧取得リクエスト.
 *
 * @property-read string $permission
 * @property-read null|int $purpose
 * @property-read array|string[] $qualifications
 * @property-read null|int $userId
 * @property-read bool $isCommunityGeneralSupportCenter
 */
class GetIndexOfficeOptionRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $userExists = Option::fromArray($input, 'permission')
            ->filter(fn ($x): bool => is_string($x) && $x !== '')
            ->map(fn (string $x): string => "user_exists:{$x}");
        return [
            'permission' => ['permission', 'authorized_permission'],
            'purpose' => ['nullable', 'purpose'],
            'qualifications' => ['nullable', 'array'],
            'qualifications.*' => ['required', 'office_qualification'],
            'userId' => ['nullable', ...$userExists],
            'isCommunityGeneralSupportCenter' => ['nullable', 'boolean_ext'],
        ];
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 自費サービス情報更新リクエスト.
 *
 * @property-read null|int $officeId
 * @property-read string $name
 * @property-read int $durationMinutes
 * @property-read array $fee
 * @property-read string $note
 */
class UpdateOwnExpenseProgramRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * リクエストを自費サービス情報に変換する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'name' => $this->name,
            'note' => $this->note ?? '',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}

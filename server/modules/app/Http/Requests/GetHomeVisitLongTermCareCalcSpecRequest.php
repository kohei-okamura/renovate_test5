<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業所算定情報（介保・訪問介護）特定リクエスト.
 *
 * @property-read string $providedIn
 */
class GetHomeVisitLongTermCareCalcSpecRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * パラメータを変換する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'providedIn' => Carbon::parse($this->providedIn),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'providedIn' => ['required', 'date_format:Y-m'],
        ];
    }
}

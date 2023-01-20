<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリを取得リクエスト.
 *
 * @property-read string $providedIn
 */
class GetLtcsHomeVisitLongTermCareDictionaryEntryRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 処理用の配列を生成する.
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

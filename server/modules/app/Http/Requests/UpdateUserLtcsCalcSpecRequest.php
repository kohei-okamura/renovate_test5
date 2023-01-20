<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 介護保険サービス：利用者別算定情報更新リクエスト
 *
 * @property-read string $effectivatedOn
 * @property-read int $locationAddition
 */
class UpdateUserLtcsCalcSpecRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'effectivatedOn' => Carbon::parse($this->effectivatedOn),
            'locationAddition' => LtcsUserLocationAddition::from($this->locationAddition),
        ];
    }

    protected function rules(array $input): array
    {
        return [
            'effectivatedOn' => ['required', 'date'],
            'locationAddition' => ['required', 'ltcs_user_location_addition'],
        ];
    }
}

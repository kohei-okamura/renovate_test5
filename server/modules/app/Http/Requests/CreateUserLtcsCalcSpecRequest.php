<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 介護保険サービス：利用者別算定情報リクエスト
 *
 * @property-read string $userId
 * @property-read int $locationAddition
 * @property-read \Domain\Common\Carbon $effectivatedOn
 */
class CreateUserLtcsCalcSpecRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 介護保険サービス：利用者別算定情報ドメインモデルを生成する
     *
     * @return \Domain\User\UserLtcsCalcSpec
     */
    public function payload(): UserLtcsCalcSpec
    {
        return new UserLtcsCalcSpec(
            id: null,
            userId: +$this->userId,
            effectivatedOn: Carbon::parse($this->effectivatedOn),
            locationAddition: LtcsUserLocationAddition::from($this->locationAddition),
            isEnabled: true,
            version: 1,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules(array $input): array
    {
        return [
            'effectivatedOn' => ['required', 'date'],
            'locationAddition' => ['required', 'ltcs_user_location_addition'],
        ];
    }
}

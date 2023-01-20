<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 障害福祉サービス：利用者別算定情報リクエスト
 *
 * @property-read string $userId
 * @property-read int $locationAddition
 * @property-read \Domain\Common\Carbon $effectivatedOn
 */
class CreateUserDwsCalcSpecRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 障害福祉サービス：利用者別算定情報ドメインモデルを生成する
     *
     * @return \Domain\User\UserDwsCalcSpec
     */
    public function payload(): UserDwsCalcSpec
    {
        return new UserDwsCalcSpec(
            id: null,
            userId: +$this->userId,
            effectivatedOn: Carbon::parse($this->effectivatedOn),
            locationAddition: DwsUserLocationAddition::from($this->locationAddition),
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
            'locationAddition' => ['required', 'dws_user_location_addition'],
        ];
    }
}

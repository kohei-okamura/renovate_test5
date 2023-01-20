<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Rounding;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Rule;

/**
 * 自治体助成情報作成リクエスト
 *
 * @property-read array $period
 * @property-read string $cityName
 * @property-read string $cityCode
 * @property-read int $subsidyType
 * @property-read int $factor
 * @property-read int $rounding
 * @property-read int $benefitRate
 * @property-read int $copayRate
 * @property-read int $benefitAmount
 * @property-read int $copayAmount
 * @property-read string $note
 */
class CreateUserDwsSubsidyRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 自治体助成情報ドメインモデルを生成する
     *
     * @return \Domain\User\UserDwsSubsidy
     */
    public function payload(): UserDwsSubsidy
    {
        $values = [
            'period' => CarbonRange::create([
                'start' => Carbon::parse($this->period['start']),
                'end' => Carbon::parse($this->period['end']),
            ]),
            'cityName' => $this->cityName,
            'cityCode' => $this->cityCode,
            'subsidyType' => UserDwsSubsidyType::from($this->subsidyType),
            'factor' => $this->subsidyType === UserDwsSubsidyType::benefitRate()->value()
                || $this->subsidyType === UserDwsSubsidyType::copayRate()->value()
                    ? UserDwsSubsidyFactor::from($this->factor)
                    : UserDwsSubsidyFactor::none(),
            'benefitRate' => $this->subsidyType === UserDwsSubsidyType::benefitRate()->value()
                ? $this->benefitRate
                : 0,
            'copayRate' => $this->subsidyType === UserDwsSubsidyType::copayRate()->value()
                ? $this->copayRate
                : 0,
            'rounding' => $this->subsidyType === UserDwsSubsidyType::benefitRate()->value()
                || $this->subsidyType === UserDwsSubsidyType::copayRate()->value()
                    ? Rounding::from($this->rounding)
                    : Rounding::none(),
            'benefitAmount' => $this->subsidyType === UserDwsSubsidyType::benefitAmount()->value()
                ? $this->benefitAmount
                : 0,
            'copayAmount' => $this->subsidyType === UserDwsSubsidyType::copayAmount()->value()
                ? $this->copayAmount
                : 0,
            'note' => $this->note ?? '',
        ];

        return UserDwsSubsidy::create($values);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(array $input): array
    {
        return [
            'period.start' => ['required', 'date'],
            'period.end' => ['required', 'date'],
            'cityName' => ['required', 'string', 'max:100'],
            'cityCode' => ['required', 'string', 'max:6'],
            'subsidyType' => ['required', 'subsidy_type'],
            'factor' => Rule::when(
                $input['subsidyType'] === UserDwsSubsidyType::benefitRate()->value()
                || $input['subsidyType'] === UserDwsSubsidyType::copayRate()->value(),
                ['required', 'user_dws_subsidy_factor']
            ),
            'benefitRate' => Rule::when(
                $input['subsidyType'] === UserDwsSubsidyType::benefitRate()->value(),
                ['required', 'integer']
            ),
            'copayRate' => Rule::when(
                $input['subsidyType'] === UserDwsSubsidyType::copayRate()->value(),
                ['required', 'integer']
            ),
            'rounding' => Rule::when(
                $input['subsidyType'] === UserDwsSubsidyType::benefitRate()->value()
                    || $input['subsidyType'] === UserDwsSubsidyType::copayRate()->value(),
                ['required', 'rounding']
            ),
            'benefitAmount' => Rule::when(
                $input['subsidyType'] === UserDwsSubsidyType::benefitAmount()->value(),
                ['required', 'integer']
            ),
            'copayAmount' => Rule::when(
                $input['subsidyType'] === UserDwsSubsidyType::copayAmount()->value(),
                ['required', 'integer']
            ),
            'note' => ['nullable', 'max:255'],
        ];
    }
}

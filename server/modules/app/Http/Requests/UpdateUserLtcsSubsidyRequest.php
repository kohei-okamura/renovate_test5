<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DefrayerCategory;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 公費情報更新リクエスト
 *
 * @property-read array $period
 * @property-read string $start
 * @property-read string $end
 * @property-read int $defrayerCategory
 * @property-read string $defrayerNumber 負担者番号
 * @property-read string recipientNumber 利用者番号
 * @property-read int $benefitRate
 * @property-read int $copay
 */
class UpdateUserLtcsSubsidyRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 公費情報ドメインモデルを生成する
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'period' => CarbonRange::create([
                'start' => Carbon::parse($this->period['start']),
                'end' => Carbon::parse($this->period['end']),
            ]),
            'defrayerCategory' => DefrayerCategory::from($this->defrayerCategory),
            'defrayerNumber' => $this->defrayerNumber,
            'recipientNumber' => $this->recipientNumber,
            'benefitRate' => $this->benefitRate,
            'copay' => $this->copay,
            'isEnabled' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(array $input): array
    {
        return [
            'period.start' => ['required', 'date'],
            'period.end' => ['required', 'date'],
            'defrayerCategory' => ['required', 'defrayer_category'],
            'defrayerNumber' => ['required', 'string', 'max:8'],
            'recipientNumber' => ['required', 'string', 'max:7'],
            'benefitRate' => ['required', 'integer', 'between:1,100'],
            'copay' => ['required', 'integer', 'min:0'],
        ];
    }
}

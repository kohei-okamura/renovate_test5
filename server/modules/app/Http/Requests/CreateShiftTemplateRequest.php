<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Option;

/**
 * 勤務シフト雛形作成リクエスト.
 *
 * @property-read int $officeId
 * @property-read bool $isCopy
 * @property-read null|array $source
 * @property-read array $range
 */
class CreateShiftTemplateRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 勤務シフト雛形リクエストパラメータを生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'officeId' => $this->officeId,
            'isCopy' => $this->isCopy,
            'source' => $this->source === null
                ? Option::none()
                : Option::from(CarbonRange::create([
                    'start' => Carbon::parse($this->source['start']),
                    'end' => Carbon::parse($this->source['end']),
                ])),
            'range' => CarbonRange::create([
                'start' => Carbon::parse($this->range['start']),
                'end' => Carbon::parse($this->range['end']),
            ]),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => [
                'required',
                'office_exists:' . Permission::createShifts(),
            ],
            'isCopy' => ['required', 'boolean'],
            'source' => ['required_if:isCopy,true,1', 'array', 'same_days_range:range'],
            'source.start' => ['required_if:isCopy,true,1', 'date', 'same_weekday:range.start'],
            'source.end' => ['required_if:isCopy,true,1', 'date', 'after_or_equal:source.start'],
            'range' => ['required', 'array'],
            'range.start' => ['required', 'date'],
            'range.end' => ['required', 'date', 'after_or_equal:range.start'],
        ];
    }
}

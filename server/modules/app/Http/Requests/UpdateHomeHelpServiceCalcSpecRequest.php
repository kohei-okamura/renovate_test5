<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業所算定情報（障害・居宅介護）更新リクエスト
 *
 * @property-read array $period
 * @property-read int $specifiedOfficeAddition
 * @property-read int $treatmentImprovementAddition
 * @property-read int $specifiedTreatmentImprovementAddition
 * @property-read int $baseIncreaseSupportAddition
 */
class UpdateHomeHelpServiceCalcSpecRequest extends StaffRequest implements ValidatesWhenResolved
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
            'period' => CarbonRange::create([
                'start' => Carbon::parse($this->period['start']),
                'end' => Carbon::parse($this->period['end']),
            ]),
            'specifiedOfficeAddition' => HomeHelpServiceSpecifiedOfficeAddition::from($this->specifiedOfficeAddition),
            'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::from($this->treatmentImprovementAddition),
            'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::from($this->specifiedTreatmentImprovementAddition),
            'baseIncreaseSupportAddition' => DwsBaseIncreaseSupportAddition::from($this->baseIncreaseSupportAddition),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'period' => ['required', 'array'],
            'period.start' => ['required', 'date'],
            'period.end' => ['required', 'date', 'after:period.start'],
            'specifiedOfficeAddition' => ['required', 'home_help_service_specified_office_addition'],
            'treatmentImprovementAddition' => ['required', 'dws_treatment_improvement_addition'],
            'specifiedTreatmentImprovementAddition' => ['required', 'dws_specified_treatment_improvement_addition'],
            'baseIncreaseSupportAddition' => ['required', 'dws_base_increase_support_addition'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'period.start' => '適用期間開始',
        ];
    }
}

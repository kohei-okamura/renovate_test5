<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業所算定情報（介保・訪問介護）登録リクエスト.
 *
 * @property-read int $officeId
 * @property-read array $period
 * @property-read int $specifiedOfficeAddition
 * @property-read int $treatmentImprovementAddition
 * @property-read int $specifiedTreatmentImprovementAddition
 * @property-read int $locationAddition
 * @property-read int $baseIncreaseSupportAddition
 */
class CreateHomeVisitLongTermCareCalcSpecRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 事業所算定情報（介保・訪問介護）を生成する.
     *
     * @return \Domain\Office\HomeVisitLongTermCareCalcSpec
     */
    public function payload(): HomeVisitLongTermCareCalcSpec
    {
        $values = [
            'officeId' => $this->officeId,
            'period' => CarbonRange::create([
                'start' => Carbon::parse($this->period['start']),
                'end' => Carbon::parse($this->period['end']),
            ]),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($this->specifiedOfficeAddition),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from($this->treatmentImprovementAddition),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from($this->specifiedTreatmentImprovementAddition),
            'locationAddition' => LtcsOfficeLocationAddition::from($this->locationAddition),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::from($this->baseIncreaseSupportAddition),
        ];

        return HomeVisitLongTermCareCalcSpec::create($values);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'period' => ['required', 'array'],
            'period.start' => ['required', 'date'],
            'period.end' => ['required', 'date', 'after:period.start'],
            'specifiedOfficeAddition' => ['required', 'home_visit_long_term_care_specified_office_addition'],
            'treatmentImprovementAddition' => ['required', 'ltcs_treatment_improvement_addition'],
            'specifiedTreatmentImprovementAddition' => ['required', 'ltcs_specified_treatment_improvement_addition'],
            'locationAddition' => ['required', 'office_location_addition'],
            'baseIncreaseSupportAddition' => ['required', 'ltcs_base_increase_support_addition'],
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

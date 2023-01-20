<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Delegates\LtcsProvisionReportFormDelegate;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

/**
 * 介護保険サービス：予実更新リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 * @property-read array $entries
 * @property-read int specifiedOfficeAddition
 * @property-read int $treatmentImprovementAddition
 * @property-read int $specifiedTreatmentImprovementAddition
 * @property-read int $baseIncreaseSupportAddition
 * @property-read int $locationAddition
 * @property-read array $plan
 * @property-read array $result
 */
class UpdateLtcsProvisionReportRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    private LtcsProvisionReportFormDelegate $delegate;

    /**
     * Constructor.
     *
     * @param \App\Http\Requests\Delegates\LtcsProvisionReportFormDelegate $delegate
     * @return void
     */
    public function __construct(LtcsProvisionReportFormDelegate $delegate)
    {
        parent::__construct();
        $this->delegate = $delegate;
    }

    /**
     * リクエストを介護保険サービス：予実に変換する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'entries' => $this->getDelegate()->convertEntryArrayToModel($this->entries),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($this->specifiedOfficeAddition),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from($this->treatmentImprovementAddition),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from($this->specifiedTreatmentImprovementAddition),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::from($this->baseIncreaseSupportAddition),
            'locationAddition' => LtcsOfficeLocationAddition::from($this->locationAddition),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: (int)$this->plan['maxBenefitExcessScore'],
                maxBenefitQuotaExcessScore: (int)$this->plan['maxBenefitQuotaExcessScore'],
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: (int)$this->result['maxBenefitExcessScore'],
                maxBenefitQuotaExcessScore: (int)$this->result['maxBenefitQuotaExcessScore'],
            ),
        ];
    }

    /**
     * バリデータインスタンスの設定.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $this->getDelegate()->setValidator($this->context(), $validator);
    }

    /**
     * デリゲートを返す.
     * TODO 本来は不要だが、単体テスト時に $delegate が初期化できない問題を回避するために用意している
     * FIXME 上記問題をスマートに解決する方法があれば修正したい
     *
     * @return \App\Http\Requests\Delegates\LtcsProvisionReportFormDelegate
     */
    public function getDelegate(): LtcsProvisionReportFormDelegate
    {
        return $this->delegate;
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $providedIn = Arr::get($input, 'providedIn');
        $rules = $this->getDelegate()->createRules($input);
        $additionalRule = array_merge(
            $rules['entries'],
            ["ltcs_service_code_specified_office_match:specifiedOfficeAddition,{$providedIn}"]
        );
        return array_merge(
            $this->getDelegate()->createRules($input),
            [
                'entries' => $additionalRule,
                'plan.maxBenefitExcessScore' => ['required', 'integer', 'min:0'],
                'plan.maxBenefitQuotaExcessScore' => ['required', 'integer', 'min:0'],
                'result.maxBenefitExcessScore' => ['required', 'integer', 'min:0'],
                'result.maxBenefitQuotaExcessScore' => ['required', 'integer', 'min:0'],
            ]
        );
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return $this->getDelegate()->getAttributes();
    }

    /** {@inheritdoc} */
    protected function messages(): array
    {
        return $this->getDelegate()->getErrorMessages();
    }
}

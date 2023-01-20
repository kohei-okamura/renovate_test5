<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Rule;
use ScalikePHP\Seq;

/**
 * 介護保険被保険者証更新リクエスト.
 *
 * @property-read int $status
 * @property-read string $insNumber
 * @property-read string $insurerNumber
 * @property-read string $insurerName
 * @property-read int $ltcsLevel
 * @property-read array $maxBenefitQuotas
 * @property-read int $ltcsInsCardServiceType
 * @property-read int $maxBenefitQuota
 * @property-read int $copayRate
 * @property-read string $effectivatedOn
 * @property-read string $issuedOn
 * @property-read string $certificatedOn
 * @property-read string $activatedOn
 * @property-read string $deactivatedOn
 * @property-read string $copayActivatedOn
 * @property-read string $copayDeactivatedOn
 * @property-read string $careManagerName
 * @property-read int $carePlanAuthorType
 * @property-read null|int $communityGeneralSupportCenterId
 * @property-read null|int $carePlanAuthorOfficeId
 */
class UpdateLtcsInsCardRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を作成する.
     *
     * @return array
     */
    public function payload(): array
    {
        $values = [
            'status' => LtcsInsCardStatus::from($this->status),
            'insNumber' => $this->insNumber,
            'insurerNumber' => $this->insurerNumber,
            'insurerName' => $this->insurerName,
            'ltcsLevel' => LtcsLevel::from($this->ltcsLevel),
            'copayRate' => $this->copayRate,
            'effectivatedOn' => Carbon::parse($this->effectivatedOn),
            'issuedOn' => Carbon::parse($this->issuedOn),
            'certificatedOn' => Carbon::parse($this->certificatedOn),
            'activatedOn' => Carbon::parse($this->activatedOn),
            'deactivatedOn' => Carbon::parse($this->deactivatedOn),
            'copayActivatedOn' => Carbon::parse($this->copayActivatedOn),
            'copayDeactivatedOn' => Carbon::parse($this->copayDeactivatedOn),
            'careManagerName' => $this->careManagerName ?? '',
            'carePlanAuthorType' => LtcsCarePlanAuthorType::from($this->carePlanAuthorType),
            'communityGeneralSupportCenterId' => Seq::from(
                LtcsLevel::supportLevel1(),
                LtcsLevel::supportLevel2(),
                LtcsLevel::target()
            )->contains(LtcsLevel::from($this->ltcsLevel))
                ? $this->communityGeneralSupportCenterId
                : null,
            'carePlanAuthorOfficeId' => LtcsCarePlanAuthorType::from($this->carePlanAuthorType) === LtcsCarePlanAuthorType::self()
                ? null
                : $this->carePlanAuthorOfficeId,
            'isEnabled' => true,
        ];
        $maxBenefitQuotas = Seq::fromArray($this->maxBenefitQuotas)
            ->map(fn ($x): LtcsInsCardMaxBenefitQuota => LtcsInsCardMaxBenefitQuota::create([
                'ltcsInsCardServiceType' => LtcsInsCardServiceType::from($x['ltcsInsCardServiceType']),
                'maxBenefitQuota' => $x['maxBenefitQuota'],
            ]));

        return $values + ['maxBenefitQuotas' => $maxBenefitQuotas->toArray()];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'status' => ['required', 'ltcs_ins_card_status'],
            'insNumber' => ['required', 'max:10'],
            'insurerNumber' => ['required', 'max:6'],
            'insurerName' => ['required', 'max:100'],
            'ltcsLevel' => ['required', 'ltcs_level'],
            'maxBenefitQuotas.*.ltcsInsCardServiceType' => ['required', 'ltcs_ins_card_service_type'],
            'maxBenefitQuotas.*.maxBenefitQuota' => ['required', 'integer'],
            'copayRate' => ['required', 'integer'],
            'effectivatedOn' => ['required', 'date', 'no_ltcs_ins_card_three_more_than_valid:' . Permission::updateLtcsInsCards()],
            'issuedOn' => ['required', 'date'],
            'certificatedOn' => ['required', 'date'],
            'activatedOn' => ['required', 'date'],
            'deactivatedOn' => ['required', 'date'],
            'copayActivatedOn' => ['required', 'date'],
            'copayDeactivatedOn' => ['required', 'date'],
            'careManagerName' => [
                'required_if:carePlanAuthorType,' . LtcsCarePlanAuthorType::careManagerOffice()->value(),
                'nullable',
                'string',
                'max:100',
            ],
            'carePlanAuthorType' => ['required', 'ltcs_care_plan_author_type'],
            'communityGeneralSupportCenterId' => [
                Rule::requiredIf(function () use ($input): bool {
                    return Seq::from(
                        LtcsLevel::supportLevel1()->value(),
                        LtcsLevel::supportLevel2()->value(),
                        LtcsLevel::target()->value()
                    )->contains($input['ltcsLevel']);
                }),
                'nullable',
                'office_exists_ignore_permissions',
            ],
            'carePlanAuthorOfficeId' => [
                'required_if:carePlanAuthorType,' . LtcsCarePlanAuthorType::careManagerOffice()->value() . ',' . LtcsCarePlanAuthorType::preventionOffice()->value(),
                'nullable',
                'office_exists_ignore_permissions',
            ],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'careManagerName' => '居宅介護支援事業所：担当者',
            'carePlanAuthorType' => '居宅サービス計画作成区分',
            'carePlanAuthorOfficeId' => '居宅介護支援事業所ID',
        ];
    }
}

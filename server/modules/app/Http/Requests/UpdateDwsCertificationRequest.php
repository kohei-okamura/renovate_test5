<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス受給者証更新リクエスト.
 *
 * @property-read int $dwsLevel
 * @property-read int $status
 * @property-read array|int[] $dwsTypes
 * @property-read string $dwsNumber
 * @property-read array $copayCoordination
 * @property-read string $cityCode
 * @property-read string $cityName
 * @property-read array $child
 * @property-read int $copayRate
 * @property-read int $copayLimit
 * @property-read int $isSubjectOfComprehensiveSupport
 * @property-read array $agreements
 * @property-read array $grants
 * @property-read string $issuedOn
 * @property-read string $effectivatedOn
 * @property-read string $activatedOn
 * @property-read string $deactivatedOn
 * @property-read string $copayActivatedOn
 * @property-read string $copayDeactivatedOn
 */
class UpdateDwsCertificationRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        $grants = Seq::fromArray($this->grants)
            ->map(fn (array $x): DwsCertificationGrant => DwsCertificationGrant::create([
                'dwsCertificationServiceType' => DwsCertificationServiceType::from($x['dwsCertificationServiceType']),
                'grantedAmount' => $x['grantedAmount'],
                'activatedOn' => Carbon::parse($x['activatedOn']),
                'deactivatedOn' => Carbon::parse($x['deactivatedOn']),
            ]))
            ->toArray();
        $agreements = Seq::fromArray($this->agreements)
            ->map(fn (array $x): DwsCertificationAgreement => DwsCertificationAgreement::create([
                'indexNumber' => $x['indexNumber'],
                'officeId' => $x['officeId'],
                'dwsCertificationAgreementType' => DwsCertificationAgreementType::from($x['dwsCertificationAgreementType']),
                'paymentAmount' => $x['paymentAmount'],
                'agreedOn' => Carbon::parse($x['agreedOn']),
                'expiredOn' => empty($x['expiredOn']) ? null : Carbon::parse($x['expiredOn']),
            ]))
            ->toArray();
        return [
            'effectivatedOn' => Carbon::parse($this->effectivatedOn),
            'status' => DwsCertificationStatus::from($this->status),
            'dwsNumber' => $this->dwsNumber,
            'dwsTypes' => Seq::fromArray($this->dwsTypes)->map(fn (int $x): DwsType => DwsType::from($x))->toArray(),
            'issuedOn' => Carbon::parse($this->issuedOn),
            'cityName' => $this->cityName,
            'cityCode' => $this->cityCode,
            'dwsLevel' => DwsLevel::from($this->dwsLevel),
            'isSubjectOfComprehensiveSupport' => (bool)$this->isSubjectOfComprehensiveSupport,
            'activatedOn' => Carbon::parse($this->activatedOn),
            'deactivatedOn' => Carbon::parse($this->deactivatedOn),
            'grants' => $grants,
            'child' => Child::create([
                'name' => new StructuredName(
                    familyName: $this->child['name']['familyName'] ?? '',
                    givenName: $this->child['name']['givenName'] ?? '',
                    phoneticFamilyName: $this->child['name']['phoneticFamilyName'] ?? '',
                    phoneticGivenName: $this->child['name']['phoneticGivenName'] ?? '',
                ),
                'birthday' => empty($this->child['birthday']) ? null : Carbon::parse($this->child['birthday']),
            ]),
            'copayLimit' => $this->copayLimit,
            'copayActivatedOn' => Carbon::parse($this->copayActivatedOn),
            'copayDeactivatedOn' => Carbon::parse($this->copayDeactivatedOn),
            'copayCoordination' => CopayCoordination::create([
                'copayCoordinationType' => CopayCoordinationType::from($this->copayCoordination['copayCoordinationType']),
                'officeId' => $this->copayCoordination['officeId'] ?? null,
            ]),
            'agreements' => $agreements,
            'isEnabled' => true,
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'effectivatedOn' => ['required', 'date'],
            'status' => ['required', 'dws_certification_status'],
            'dwsNumber' => ['required', 'digits:10'],
            'dwsTypes' => ['required', 'array'],
            'dwsTypes.*' => ['required', 'dws_type'],
            'issuedOn' => ['required', 'date'],
            'cityName' => ['required', 'max:100'],
            'cityCode' => ['required', 'digits:6'],
            'dwsLevel' => ['required', 'dws_level'],
            'isSubjectOfComprehensiveSupport' => ['required', 'boolean'],
            'activatedOn' => ['required', 'date'],
            'deactivatedOn' => ['required', 'date'],
            'grants' => ['required', 'array'],
            'grants.*' => [
                'no_dws_certification_grants_duplicate:grants',
                'dws_certification_grant_exclusive:effectivatedOn,dwsLevel,isSubjectOfComprehensiveSupport',
            ],
            'grants.*.dwsCertificationServiceType' => ['required', 'dws_certification_service_type'],
            'grants.*.grantedAmount' => ['required', 'max:255'],
            'grants.*.activatedOn' => ['required', 'date'],
            'grants.*.deactivatedOn' => ['required', 'date'],
            'child.name.familyName' => ['nullable', 'string', 'max:100'],
            'child.name.givenName' => ['nullable', 'string', 'max:100'],
            'child.name.phoneticFamilyName' => ['nullable', 'max:100', 'katakana'],
            'child.name.phoneticGivenName' => ['nullable', 'max:100', 'katakana'],
            'child.birthday' => ['nullable', 'date'],
            'copayLimit' => ['required', 'integer'],
            'copayActivatedOn' => ['required', 'date'],
            'copayDeactivatedOn' => ['required', 'date'],
            'copayCoordination.copayCoordinationType' => ['required', 'copay_coordination_type'],
            'copayCoordination.officeId' => [
                Rule::requiredIf(function () use ($input): bool {
                    $x = Arr::get($input, 'copayCoordination.copayCoordinationType');
                    return $x === CopayCoordinationType::internal()->value()
                        || $x === CopayCoordinationType::external()->value();
                }),
                'office_exists_ignore_permissions',
            ],
            'agreements' => ['required', 'array'],
            'agreements.*.indexNumber' => ['required', 'integer', 'between:1,99'],
            'agreements.*.officeId' => ['required', 'office_exists:' . Permission::updateDwsCertifications()],
            'agreements.*.dwsCertificationAgreementType' => [
                'required',
                'dws_certification_agreement_type',
                'dws_certification_agreement_type_dws_level:effectivatedOn,agreements.*.expiredOn,dwsLevel,isSubjectOfComprehensiveSupport',
            ],
            'agreements.*.paymentAmount' => ['required', 'integer'],
            'agreements.*.agreedOn' => ['required', 'date'],
            'agreements.*.expiredOn' => ['nullable', 'date'],
        ];
    }
}

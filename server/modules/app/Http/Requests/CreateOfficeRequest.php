<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Office\Office;
use Domain\Office\OfficeDwsCommAccompanyService;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeLtcsCareManagementService;
use Domain\Office\OfficeLtcsCompHomeVisitingService;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeLtcsPreventionService;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Rule;
use ScalikePHP\Seq;

/**
 * 事業所作成リクエスト.
 *
 * @property-read string $name
 * @property-read string $abbr
 * @property-read string $phoneticName
 * @property-read string $corporationName
 * @property-read string $phoneticCorporationName
 * @property-read int $purpose
 * @property-read string $postcode
 * @property-read int $prefecture
 * @property-read string $city
 * @property-read string $street
 * @property-read string $apartment
 * @property-read string $tel
 * @property-read string $fax
 * @property-read string $email
 * @property-read array|string[] $qualifications
 * @property-read int $officeGroupId
 * @property-read null|array $dwsGenericService
 * @property-read null|array $dwsCommAccompanyService
 * @property-read null|array $ltcsCareManagementService
 * @property-read null|array $ltcsHomeVisitLongTermCareService
 * @property-read null|array $ltcsCompHomeVisitingService
 * @property-read null|array $ltcsPreventionService
 * @property-read int $status
 */
class CreateOfficeRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    public const PREFECTURE_NONE = 0;

    /**
     * 事業所を生成する.
     *
     * @return \Domain\Office\Office
     */
    public function payload(): Office
    {
        $values = [
            'name' => $this->name,
            'abbr' => $this->abbr ?? '',
            'phoneticName' => $this->phoneticName,
            'corporationName' => $this->isInternal($this->toAssoc())
                ? ''
                : ($this->corporationName ?? ''),
            'phoneticCorporationName' => $this->isInternal($this->toAssoc())
                ? ''
                : ($this->phoneticCorporationName ?? ''),
            'purpose' => Purpose::from($this->purpose),
            'addr' => new Addr(
                postcode: $this->postcode ?? '',
                prefecture: $this->prefecture === null
                    ? Prefecture::from(self::PREFECTURE_NONE)
                    : Prefecture::from($this->prefecture),
                city: $this->city ?? '',
                street: $this->street ?? '',
                apartment: $this->apartment ?? '',
            ),
            'location' => Location::create([
                'lat' => 0,
                'lng' => 0,
            ]),
            'tel' => $this->tel ?? '',
            'fax' => $this->fax ?? '',
            'email' => $this->isInternal($this->toAssoc())
                ? $this->email
                : '', // 自社以外は入力受け付けず
            'qualifications' => Seq::fromArray($this->qualifications)
                ->map(fn (string $x): OfficeQualification => OfficeQualification::from($x))
                ->toArray(),
            'officeGroupId' => $this->isInternal($this->toAssoc())
                ? $this->officeGroupId
                : null, // 自社以外は入力受け付けず
            'dwsGenericService' => $this
                ->hasQualifications(
                    $this->toAssoc(),
                    OfficeQualification::dwsHomeHelpService(),
                    OfficeQualification::dwsVisitingCareForPwsd(),
                    OfficeQualification::dwsOthers()
                )
                ? $this->officeDwsGenericService($this->toAssoc())
                : null,
            'dwsCommAccompanyService' => $this
                ->hasQualifications(
                    $this->toAssoc(),
                    OfficeQualification::dwsCommAccompany()
                )
                ? $this->dwsCommAccompanyService($this->toAssoc())
                : null,
            'ltcsCareManagementService' => $this
                ->hasQualifications(
                    $this->toAssoc(),
                    OfficeQualification::ltcsCareManagement()
                )
                ? $this->ltcsCareManagementService($this->toAssoc())
                : null,
            'ltcsHomeVisitLongTermCareService' => $this
                ->hasQualifications(
                    $this->toAssoc(),
                    OfficeQualification::ltcsHomeVisitLongTermCare()
                )
                ? $this->ltcsHomeVisitLongTermCareService($this->toAssoc())
                : null,
            'ltcsCompHomeVisitingService' => $this
                ->hasQualifications(
                    $this->toAssoc(),
                    OfficeQualification::ltcsCompHomeVisiting()
                )
                ? $this->ltcsCompHomeVisitingService($this->toAssoc())
                : null,
            'ltcsPreventionService' => $this->hasQualifications($this->toAssoc(), OfficeQualification::ltcsPrevention())
                ? $this->ltcsPreventionService($this->toAssoc())
                : null,
            'status' => OfficeStatus::from($this->status),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];

        return Office::create($values);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'name' => ['required', 'max:200'],
            'abbr' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'max:200',
            ],
            'phoneticName' => ['required', 'max:200', 'katakana'],
            'corporationName' => ['nullable', 'max:200'],
            'phoneticCorporationName' => ['nullable', 'max:200', 'katakana'],
            'purpose' => ['required', 'purpose'],
            'postcode' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'postcode',
            ],
            'prefecture' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'prefecture',
            ],
            'city' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'max:200',
            ],
            'street' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'max:200',
            ],
            'apartment' => ['nullable', 'max:200'],
            'tel' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'phone_number',
            ],
            'fax' => ['nullable', 'phone_number'],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'qualifications.*' => ['required', 'office_qualification'],
            'officeGroupId' => [
                Rule::requiredIf(fn (): bool => $this->isInternal($input)),
                'nullable',
                'office_group_exists',
            ],
            'dwsGenericService.dwsAreaGradeId' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications(
                            $input,
                            OfficeQualification::dwsHomeHelpService(),
                            OfficeQualification::dwsVisitingCareForPwsd(),
                            OfficeQualification::dwsOthers()
                        );
                }),
                'nullable',
                'dws_area_grade_exists',
            ],
            'dwsGenericService.code' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->hasQualifications(
                        $input,
                        OfficeQualification::dwsHomeHelpService(),
                        OfficeQualification::dwsVisitingCareForPwsd(),
                        OfficeQualification::dwsOthers()
                    );
                }),
                'nullable',
                'ascii_alpha_num',
                'size:10',
            ],
            'dwsGenericService.openedOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications(
                            $input,
                            OfficeQualification::dwsHomeHelpService(),
                            OfficeQualification::dwsVisitingCareForPwsd(),
                            OfficeQualification::dwsOthers()
                        );
                }),
                'nullable',
                'date',
            ],
            'dwsGenericService.designationExpiredOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications(
                            $input,
                            OfficeQualification::dwsHomeHelpService(),
                            OfficeQualification::dwsVisitingCareForPwsd(),
                            OfficeQualification::dwsOthers()
                        );
                }),
                'nullable',
                'date',
            ],
            'dwsCommAccompanyService.code' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->hasQualifications($input, OfficeQualification::dwsCommAccompany());
                }),
                'nullable',
                'ascii_alpha_num',
                'size:10',
            ],
            'dwsCommAccompanyService.openedOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::dwsCommAccompany());
                }),
                'nullable',
                'date',
            ],
            'dwsCommAccompanyService.designationExpiredOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::dwsCommAccompany());
                }),
                'nullable',
                'date',
            ],
            'ltcsCareManagementService.ltcsAreaGradeId' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsCareManagement());
                }),
                'nullable',
                'ltcs_area_grade_exists',
            ],
            'ltcsCareManagementService.code' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->hasQualifications($input, OfficeQualification::ltcsCareManagement());
                }),
                'nullable',
                'ascii_alpha_num',
                'size:10',
            ],
            'ltcsCareManagementService.openedOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsCareManagement());
                }),
                'nullable',
                'date',
            ],
            'ltcsCareManagementService.designationExpiredOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsCareManagement());
                }),
                'nullable',
                'date',
            ],
            'ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsHomeVisitLongTermCare());
                }),
                'nullable',
                'ltcs_area_grade_exists',
            ],
            'ltcsHomeVisitLongTermCareService.code' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->hasQualifications($input, OfficeQualification::ltcsHomeVisitLongTermCare());
                }),
                'nullable',
                'ascii_alpha_num',
                'size:10',
            ],
            'ltcsHomeVisitLongTermCareService.openedOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsHomeVisitLongTermCare());
                }),
                'nullable',
                'date',
            ],
            'ltcsHomeVisitLongTermCareService.designationExpiredOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsHomeVisitLongTermCare());
                }),
                'nullable',
                'date',
            ],
            'ltcsCompHomeVisitingService.code' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->hasQualifications($input, OfficeQualification::ltcsCompHomeVisiting());
                }),
                'nullable',
                'ascii_alpha_num',
                'size:10',
            ],
            'ltcsCompHomeVisitingService.openedOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsCompHomeVisiting());
                }),
                'nullable',
                'date',
            ],
            'ltcsCompHomeVisitingService.designationExpiredOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsCompHomeVisiting());
                }),
                'nullable',
                'date',
            ],
            'ltcsPreventionService.code' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->hasQualifications($input, OfficeQualification::ltcsPrevention());
                }),
                'nullable',
                'ascii_alpha_num',
                'size:10',
            ],
            'ltcsPreventionService.openedOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsPrevention());
                }),
                'nullable',
                'date',
            ],
            'ltcsPreventionService.designationExpiredOn' => [
                Rule::requiredIf(function () use ($input): bool {
                    return $this->isInternal($input)
                        && $this->hasQualifications($input, OfficeQualification::ltcsPrevention());
                }),
                'nullable',
                'date',
            ],
            'status' => ['required', 'office_status'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'dwsGenericService.code' => '障害福祉サービス：事業所番号',
            'dwsCommAccompanyService.code' => '障害福祉サービス：移動支援（地域生活支援事業）：事業所番号',
            'ltcsCareManagementService.code' => '介護保険サービス：訪問介護：事業所番号',
            'ltcsHomeVisitLongTermCareService.code' => '介護保険サービス：居宅介護支援：事業所番号',
            'ltcsCompHomeVisitingService.code' => '介護保険サービス：訪問型サービス（総合事業）：事業所番号',
            'ltcsPreventionService.code' => '介護保険サービス：介護予防支援：事業所番号',
        ];
    }

    /**
     * 特定の指定区分がパラメータに含まれるか判定.
     *
     * @param array $input
     * @param \Domain\Office\OfficeQualification ...$qualifications
     * @return bool
     */
    private function hasQualifications(array $input, OfficeQualification ...$qualifications): bool
    {
        return isset($input['qualifications'])
            && is_array($input['qualifications'])
            && Seq::fromArray($qualifications)
                ->find(fn (OfficeQualification $x): bool => in_array($x->value(), $input['qualifications'], true))
                ->nonEmpty();
    }

    /**
     * 事業区分が自社かどうか判定.
     *
     * @param array $input
     * @return bool
     */
    private function isInternal(array $input): bool
    {
        return Purpose::isValid($input['purpose']) && Purpose::from($input['purpose']) === Purpose::internal();
    }

    /**
     * 事業所：障害福祉サービスを生成.
     *
     * @param array $input
     * @return \Domain\Office\OfficeDwsGenericService
     */
    private function officeDwsGenericService(array $input): OfficeDwsGenericService
    {
        return OfficeDwsGenericService::create([
            'dwsAreaGradeId' => $this->isInternal($input)
                ? $this->dwsGenericService['dwsAreaGradeId']
                : null,
            'code' => $this->dwsGenericService['code'],
            'openedOn' => $this->isInternal($input)
                ? $this->dwsGenericService['openedOn']
                : null,
            'designationExpiredOn' => $this->isInternal($input)
                ? $this->dwsGenericService['designationExpiredOn']
                : null,
        ]);
    }

    /**
     * 事業所：障害福祉サービス（地域生活支援事業・移動支援）を生成.
     *
     * @param array $input
     * @return \Domain\Office\OfficeDwsCommAccompanyService
     */
    private function dwsCommAccompanyService(array $input): OfficeDwsCommAccompanyService
    {
        return OfficeDwsCommAccompanyService::create([
            'code' => $this->dwsCommAccompanyService['code'],
            'openedOn' => $this->isInternal($input)
                ? $this->dwsCommAccompanyService['openedOn']
                : null,
            'designationExpiredOn' => $this->isInternal($input)
                ? $this->dwsCommAccompanyService['designationExpiredOn']
                : null,
        ]);
    }

    /**
     * 事業所：介護保険サービス：居宅介護支援を生成.
     *
     * @param array $input
     * @return \Domain\Office\OfficeLtcsCareManagementService
     */
    private function ltcsCareManagementService(array $input): OfficeLtcsCareManagementService
    {
        return OfficeLtcsCareManagementService::create([
            'code' => $this->ltcsCareManagementService['code'],
            'openedOn' => $this->isInternal($input)
                ? $this->ltcsCareManagementService['openedOn']
                : null,
            'designationExpiredOn' => $this->isInternal($input)
                ? $this->ltcsCareManagementService['designationExpiredOn']
                : null,
            'ltcsAreaGradeId' => $this->isInternal($input)
                ? $this->ltcsCareManagementService['ltcsAreaGradeId']
                : null,
        ]);
    }

    /**
     * 事業所：介護保険サービス：訪問介護を生成.
     *
     * @param array $input
     * @return \Domain\Office\OfficeLtcsHomeVisitLongTermCareService
     */
    private function ltcsHomeVisitLongTermCareService(array $input): OfficeLtcsHomeVisitLongTermCareService
    {
        return OfficeLtcsHomeVisitLongTermCareService::create([
            'code' => $this->ltcsHomeVisitLongTermCareService['code'],
            'openedOn' => $this->isInternal($input)
                ? $this->ltcsHomeVisitLongTermCareService['openedOn']
                : null,
            'designationExpiredOn' => $this->isInternal($input)
                ? $this->ltcsHomeVisitLongTermCareService['designationExpiredOn']
                : null,
            'ltcsAreaGradeId' => $this->isInternal($input)
                ? $this->ltcsHomeVisitLongTermCareService['ltcsAreaGradeId']
                : null,
        ]);
    }

    /**
     * 事業所：介護保険サービス：訪問型サービス（総合事業）を生成.
     *
     * @param array $input
     * @return \Domain\Office\OfficeLtcsCompHomeVisitingService
     */
    private function ltcsCompHomeVisitingService(array $input): OfficeLtcsCompHomeVisitingService
    {
        return OfficeLtcsCompHomeVisitingService::create([
            'code' => $this->ltcsCompHomeVisitingService['code'],
            'openedOn' => $this->isInternal($input)
                ? $this->ltcsCompHomeVisitingService['openedOn']
                : null,
            'designationExpiredOn' => $this->isInternal($input)
                ? $this->ltcsCompHomeVisitingService['designationExpiredOn']
                : null,
        ]);
    }

    /**
     * 事業所：介護保険サービス：介護予防支援を生成.
     *
     * @param array $input
     * @return \Domain\Office\OfficeLtcsPreventionService
     */
    private function ltcsPreventionService(array $input): OfficeLtcsPreventionService
    {
        return new OfficeLtcsPreventionService(
            code: $this->ltcsPreventionService['code'],
            openedOn: $this->isInternal($input)
                ? Carbon::parse($this->ltcsPreventionService['openedOn'])
                : null,
            designationExpiredOn: $this->isInternal($input)
                ? Carbon::parse($this->ltcsPreventionService['designationExpiredOn'])
                : null,
        );
    }
}

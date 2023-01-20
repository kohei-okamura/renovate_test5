<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Permission\Permission;
use Domain\Staff\Certification;
use Domain\Staff\StaffStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * スタッフ更新リクエスト.
 *
 * @property-read string $familyName
 * @property-read string $givenName
 * @property-read string $phoneticFamilyName
 * @property-read string $phoneticGivenName
 * @property-read int $sex
 * @property-read string $birthday
 * @property-read string $postcode
 * @property-read int $prefecture
 * @property-read string $city
 * @property-read string $street
 * @property-read string $apartment
 * @property-read array $location
 * @property-read string $tel
 * @property-read string $fax
 * @property-read string $email
 * @property-read string $password
 * @property-read int $status
 * @property-read null|array|int[] $certifications
 * @property-read null|string $employeeNumber
 * @property-read null|array|int[] $roleIds
 * @property-read null|array|int[] $officeIds 事業所ID
 * @property-read null|array|int[] $officeGroupIds 事業所グループID
 */
class UpdateStaffRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    public function payload(): array
    {
        return [
            'name' => new StructuredName(
                familyName: $this->familyName,
                givenName: $this->givenName,
                phoneticFamilyName: $this->phoneticFamilyName,
                phoneticGivenName: $this->phoneticGivenName,
            ),
            'sex' => Sex::from($this->sex),
            'birthday' => Carbon::parse($this->birthday),
            'addr' => new Addr(
                postcode: $this->postcode ?? '',
                prefecture: Prefecture::from($this->prefecture),
                city: $this->city ?? '',
                street: $this->street ?? '',
                apartment: $this->apartment ?? '',
            ),
            'tel' => $this->tel,
            'fax' => $this->fax ?? '',
            'email' => $this->email,
            'status' => StaffStatus::from($this->status),
            'certifications' => empty($this->certifications)
                ? []
                : Seq::fromArray($this->certifications)
                    ->map(fn (int $x): Certification => Certification::from($x)),
            'employeeNumber' => $this->employeeNumber ?? '',
            'roleIds' => $this->roleIds ?? [],
            'officeIds' => $this->officeIds ?? [],
            'officeGroupIds' => $this->officeGroupIds ?? [],
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $staffId = Arr::get($input, 'id');
        return [
            'familyName' => ['required', 'max:100'],
            'givenName' => ['required', 'max:100'],
            'phoneticFamilyName' => ['required', 'max:100', 'katakana'],
            'phoneticGivenName' => ['required', 'max:100', 'katakana'],
            'sex' => ['required', 'sex'],
            'birthday' => ['required', 'date'],
            'postcode' => ['required', 'postcode'],
            'prefecture' => ['required', 'prefecture'],
            'city' => ['required', 'max:200'],
            'street' => ['required', 'max:200'],
            'apartment' => ['max:200'],
            'tel' => ['required', 'phone_number'],
            'fax' => ['nullable', 'phone_number'],
            'email' => ['required', 'email', 'max:255', "email_address_is_not_used_by_any_staff:{$staffId}"],
            'status' => ['required', 'staff_status'],
            'certifications.*' => ['nullable', 'certification'],
            'employeeNumber' => ['nullable', 'max:20'],
            'roleIds' => ['nullable', 'role_exists'],
            'officeIds' => ['nullable', 'office_exists:' . Permission::updateStaffs()],
            'officeGroupIds' => ['nullable', 'office_group_exists'],
        ];
    }
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Staff\Certification;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * スタッフ作成リクエスト.
 *
 * @property-read string $email メールアドレス
 * @property-read string $password パスワード
 * @property-read string $familyName 性
 * @property-read string $givenName 名
 * @property-read string $phoneticFamilyName フリガナ：性
 * @property-read string $phoneticGivenName フリガナ：名
 * @property-read int $sex 性別
 * @property-read string $birthday 生年月日
 * @property-read string $postcode 郵便番号
 * @property-read int $prefecture 都道府県
 * @property-read string $city 市区町村
 * @property-read string $street 町名・番地
 * @property-read string $apartment 建物名など
 * @property-read string $tel 電話番号
 * @property-read string $fax FAX 番号
 * @property-read int $status 状態
 * @property-read array $certifications 資格
 * @property-read int $invitationId 招待ID
 * @property-read string $token トークン
 */
class CreateStaffRequest extends OrganizationRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * スタッフを生成する.
     *
     * @return \Domain\Staff\Staff
     */
    public function payload(): Staff
    {
        return Staff::create([
            'employeeNumber' => '',
            'name' => new StructuredName(
                familyName: $this->familyName,
                givenName: $this->givenName,
                phoneticFamilyName: $this->phoneticFamilyName,
                phoneticGivenName: $this->phoneticGivenName,
            ),
            'sex' => Sex::from($this->sex),
            'birthday' => Carbon::parse($this->birthday),
            'addr' => new Addr(
                postcode: $this->postcode,
                prefecture: Prefecture::from($this->prefecture),
                city: $this->city,
                street: $this->street,
                apartment: $this->apartment ?? '',
            ),
            'location' => Location::create([
                'lat' => null,
                'lng' => null,
            ]),
            'tel' => $this->tel,
            'fax' => $this->fax ?? '',
            'certifications' => Seq::fromArray($this->certifications)
                ->map(fn (int $x): Certification => Certification::from($x))
                ->toArray(),
            'password' => Password::fromString($this->password),
            'isVerified' => true,
            'isEnabled' => true,
            'version' => 1,
            'status' => empty($this->status) ? StaffStatus::active() : StaffStatus::from($this->status),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'password' => ['required', 'min:8'],
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
            'fax' => ['phone_number'],
            'status' => ['nullable', 'staff_status'],
            'certifications.*' => ['nullable', 'certification'],
            'invitationId' => ['required', 'invitation_exists', 'invitation_email_address_is_not_used_by_any_staff'],
            'token' => ['required', 'invitation_token_match:invitationId'],
        ];
    }
}

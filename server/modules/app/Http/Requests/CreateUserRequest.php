<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\User;
use Domain\User\UserBillingDestination;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Rule;
use ScalikePHP\Seq;

/**
 * 利用者登録クエスト.
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
 * @property-read array $contacts
 * @property-read array $billingDestination
 */
class CreateUserRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 利用者を生成する.
     *
     * @return \Domain\User\User
     */
    public function payload(): User
    {
        return User::create([
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
                'lat' => 0,
                'lng' => 0,
            ]),
            'contacts' => Seq::fromArray($this->contacts)
                ->map(fn (array $x): Contact => Contact::create([
                    'tel' => $x['tel'] ?? '',
                    'relationship' => ContactRelationship::from($x['relationship']),
                    'name' => $x['name'],
                ]))
                ->toArray(),
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::from($this->billingDestination['destination']),
                'paymentMethod' => PaymentMethod::from($this->billingDestination['paymentMethod']),
                'contractNumber' => $this->billingDestination['paymentMethod'] === PaymentMethod::withdrawal()->value()
                    ? $this->billingDestination['contractNumber']
                    : '',
                'corporationName' => $this->billingDestination['destination'] === BillingDestination::corporation()->value()
                    ? $this->billingDestination['corporationName']
                    : '',
                'agentName' => $this->billingDestination['destination'] !== BillingDestination::theirself()->value()
                    ? $this->billingDestination['agentName']
                    : '',
                'addr' => $this->billingDestination['destination'] !== BillingDestination::theirself()->value()
                    ? new Addr(
                        postcode: $this->billingDestination['postcode'],
                        prefecture: Prefecture::from($this->billingDestination['prefecture']),
                        city: $this->billingDestination['city'],
                        street: $this->billingDestination['street'],
                        apartment: $this->billingDestination['apartment'] ?? '',
                    )
                    : null,
                'tel' => $this->billingDestination['destination'] !== BillingDestination::theirself()->value()
                    ? $this->billingDestination['tel']
                    : '',
            ]),
            'isEnabled' => true,
        ]);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $billingDestination = $input['billingDestination'];
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
            'apartment' => ['nullable', 'max:200'],
            'contacts' => ['required', 'array', 'max:3'],
            'contacts.*.tel' => ['nullable', 'phone_number'],
            'contacts.*.relationship' => [
                'required_with:contacts.*.tel',
                'contact_relationship',
            ],
            'contacts.*.name' => [
                'required_if:contacts.*.relationship,' . ContactRelationship::family()->value() . ',' . ContactRelationship::lawyer()->value() . ',' . ContactRelationship::others()->value(),
                'string',
            ],
            'billingDestination.destination' => ['required', 'billing_destination'],
            'billingDestination.paymentMethod' => ['required', 'payment_method'],
            'billingDestination.contractNumber' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['paymentMethod'] === PaymentMethod::withdrawal()->value()
                ),
                'nullable',
                'string',
                'size:10',
            ],
            'billingDestination.corporationName' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'string',
                'max:200',
            ],
            'billingDestination.agentName' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::agent()->value()
                        || $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'string',
                'max:100',
            ],
            'billingDestination.postcode' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::agent()->value()
                        || $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'postcode',
            ],
            'billingDestination.prefecture' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::agent()->value()
                        || $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'prefecture',
            ],
            'billingDestination.city' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::agent()->value()
                        || $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'string',
                'max:200',
            ],
            'billingDestination.street' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::agent()->value()
                        || $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'string',
                'max:200',
            ],
            'billingDestination.apartment' => [
                'nullable',
                'string',
                'max:200',
            ],
            'billingDestination.tel' => [
                Rule::requiredIf(
                    fn (): bool => $billingDestination['destination'] === BillingDestination::agent()->value()
                        || $billingDestination['destination'] === BillingDestination::corporation()->value()
                ),
                'nullable',
                'phone_number',
            ],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'contacts.*.tel' => '電話番号',
            'contacts.*.relationship' => '続柄・関係',
            'contacts.*.name' => '名前',
            'billingDestination.contractNumber' => '契約者番号',
        ];
    }
}

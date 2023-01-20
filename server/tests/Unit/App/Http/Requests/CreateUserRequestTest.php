<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
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
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * CreateUserRequest のテスト
 */
final class CreateUserRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    protected CreateUserRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new CreateUserRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return User', function (): void {
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->defaultInput())
            );
            $this->assertModelStrictEquals(
                $this->expectedPayload($this->defaultInput()),
                $this->request->payload()
            );
        });
        $this->should(
            'return User when non-required param is null or empty',
            function ($key): void {
                foreach (['', null] as $value) {
                    $input = $this->defaultInput();
                    Arr::set($input, $key, $value);
                    $input = tap($this->defaultInput(), function (array &$defaultInput) use ($key, $value): void {
                        Arr::set($defaultInput, $key, $value);
                    });
                    // リクエスト内容を反映させるために initialize() を利用する
                    $this->request->initialize(
                        [],
                        [],
                        [],
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json'],
                        Json::encode($input)
                    );
                    $this->assertModelStrictEquals(
                        $this->expectedPayload($input),
                        $this->request->payload()
                    );
                }
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when contacts.0.tel' => ['contacts.0.tel'],
                    'when billingDestination.apartment' => ['billingDestination.apartment'],
                ],
            ]
        );
        $this->should(
            'return User when non-required param is undefined',
            function ($key): void {
                $input = tap($this->defaultInput(), function (array &$defaultInput) use ($key): void {
                    Arr::forget($defaultInput, $key);
                });
                // リクエスト内容を反映させるために initialize() を利用する
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input)
                );
                $this->assertModelStrictEquals(
                    $this->expectedPayload($input),
                    $this->request->payload()
                );
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when contacts.0.tel' => ['contacts.0.tel'],
                    'when billingDestination.apartment' => ['billingDestination.apartment'],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when familyName is empty' => [
                ['familyName' => ['入力してください。']],
                ['familyName' => ''],
                ['familyName' => '新垣'],
            ],
            'when familyName is longer than 100' => [
                ['familyName' => ['100文字以内で入力してください。']],
                ['familyName' => str_repeat('山', 101)],
                ['familyName' => str_repeat('山', 100)],
            ],
            'when givenName is empty' => [
                ['givenName' => ['入力してください。']],
                ['givenName' => ''],
                ['givenName' => '新垣'],
            ],
            'when givenName is longer than 100' => [
                ['givenName' => ['100文字以内で入力してください。']],
                ['givenName' => str_repeat('山', 101)],
                ['givenName' => str_repeat('山', 100)],
            ],
            'when phoneticFamilyName is empty' => [
                ['phoneticFamilyName' => ['入力してください。']],
                ['phoneticFamilyName' => ''],
                ['phoneticFamilyName' => 'シンガキ'],
            ],
            'when phoneticFamilyName is longer than 100' => [
                ['phoneticFamilyName' => ['100文字以内で入力してください。']],
                ['phoneticFamilyName' => str_repeat('ア', 101)],
                ['phoneticFamilyName' => str_repeat('ア', 100)],
            ],
            'when phoneticFamilyName contains non-katakana character(s)' => [
                ['phoneticFamilyName' => ['カタカナで入力してください。']],
                ['phoneticFamilyName' => 'シンガキ栄作'],
                ['phoneticFamilyName' => 'シンガキ'],
            ],
            'when phoneticGivenName is empty' => [
                ['phoneticGivenName' => ['入力してください。']],
                ['phoneticGivenName' => ''],
                ['phoneticGivenName' => 'エイサク'],
            ],
            'when phoneticGivenName is longer than 100' => [
                ['phoneticGivenName' => ['100文字以内で入力してください。']],
                ['phoneticGivenName' => str_repeat('ア', 101)],
                ['phoneticGivenName' => str_repeat('ア', 100)],
            ],
            'when phoneticGivenName contains non-katakana character(s)' => [
                ['phoneticGivenName' => ['カタカナで入力してください。']],
                ['phoneticGivenName' => 'シンガキ栄作'],
                ['phoneticGivenName' => 'エイサク'],
            ],
            'when sex is empty' => [
                ['sex' => ['入力してください。']],
                ['sex' => ''],
            ],
            'when unknown sex given' => [
                ['sex' => ['性別を指定してください。']],
                ['sex' => 999],
                ['sex' => Sex::male()->value()],
            ],
            'when birthday is empty' => [
                ['birthday' => ['入力してください。']],
                ['birthday' => ''],
                ['birthday' => '2000-02-29'],
            ],
            'when invalid birthday given' => [
                ['birthday' => ['正しい日付を入力してください。']],
                ['birthday' => '1999-02-29'],
                ['birthday' => '2000-02-29'],
            ],
            'when postcode is empty' => [
                ['postcode' => ['入力してください。']],
                ['postcode' => ''],
                ['postcode' => '351-0106'],
            ],
            'when invalid postcode given' => [
                ['postcode' => ['郵便番号は7桁で入力してください。']],
                ['postcode' => '133-005'],
                ['postcode' => '133-0051'],
            ],
            'when prefecture is empty' => [
                ['prefecture' => ['入力してください。']],
                ['prefecture' => ''],
            ],
            'when invalid prefecture given' => [
                ['prefecture' => ['都道府県を指定してください。']],
                ['prefecture' => 99],
                ['prefecture' => Prefecture::tokyo()->value()],
            ],
            'when city is empty' => [
                ['city' => ['入力してください。']],
                ['city' => ''],
            ],
            'when city is longer than 200' => [
                ['city' => ['200文字以内で入力してください。']],
                ['city' => str_repeat('江', 201)],
                ['city' => str_repeat('江', 200)],
            ],
            'when street is empty' => [
                ['street' => ['入力してください。']],
                ['street' => ''],
            ],
            'when street is longer than 200' => [
                ['street' => ['200文字以内で入力してください。']],
                ['street' => str_repeat('北', 201)],
                ['street' => str_repeat('北', 200)],
            ],
            'when apartment is longer than 200' => [
                ['apartment' => ['200文字以内で入力してください。']],
                ['apartment' => str_repeat('西', 201)],
                ['apartment' => str_repeat('西', 200)],
            ],
            'when contacts is not array' => [
                ['contacts' => ['配列にしてください。', '3個以下を選択してください。']],
                ['contacts' => 'error'],
                ['contacts' => $this->defaultInput()['contacts']],
            ],
            'when contacts size is longer than 3' => [
                ['contacts' => ['3個以下を選択してください。']],
                [
                    'contacts' => [
                        [
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family()->value(),
                            'name' => '田中花子',
                        ],
                        [
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family()->value(),
                            'name' => '田中花子',
                        ],
                        [
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family()->value(),
                            'name' => '田中花子',
                        ],
                        [
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family()->value(),
                            'name' => '田中花子',
                        ],
                    ],
                ],
                ['contacts' => $this->defaultInput()['contacts']],
            ],
            'when contacts.0.tel is not phone number' => [
                ['contacts.0.tel' => ['正しい値を入力してください。']],
                ['contacts.0.tel' => 'error'],
                ['contacts.0.tel' => '01-2345-6789'],
            ],
            'when contacts.0.relationship is empty and tel is not empty' => [
                ['contacts.0.relationship' => ['電話番号が存在する時、続柄・関係は必ず入力してください。']],
                ['contacts.0.relationship' => '', 'contacts.0.tel' => '01-2345-6789'],
                [
                    'contacts.0.relationship' => ContactRelationship::family()->value(),
                    'contacts.0.tel' => '01-2345-6789',
                ],
            ],
            'when contacts.0.relationship is invalid' => [
                ['contacts.0.relationship' => ['連絡先電話番号：続柄・関係を指定してください。']],
                ['contacts.0.relationship' => self::INVALID_ENUM_VALUE],
                ['contacts.0.relationship' => ContactRelationship::family()->value()],
            ],
            'when contacts.0.name is not string' => [
                ['contacts.0.name' => ['文字列で入力してください。']],
                ['contacts.0.name' => 123],
                ['contacts.0.name' => '田中花子'],
            ],
            'when contacts.0.name is empty and contacts.0.relationship is family' => [
                ['contacts.0.name' => ['続柄・関係が' . ContactRelationship::family()->value() . 'の時、名前は必ず入力してください。']],
                ['contacts.0.relationship' => ContactRelationship::family()->value(), 'contacts.0.name' => ''],
                ['contacts.0.name' => '田中花子'],
            ],
            'when contacts.0.name is empty and contacts.0.relationship is lawyer' => [
                ['contacts.0.name' => ['続柄・関係が' . ContactRelationship::lawyer()->value() . 'の時、名前は必ず入力してください。']],
                ['contacts.0.relationship' => ContactRelationship::lawyer()->value(), 'contacts.0.name' => ''],
                ['contacts.0.name' => '田中花子'],
            ],
            'when contacts.0.name is empty and contacts.0.relationship is others' => [
                ['contacts.0.name' => ['続柄・関係が' . ContactRelationship::others()->value() . 'の時、名前は必ず入力してください。']],
                ['contacts.0.relationship' => ContactRelationship::others()->value(), 'contacts.0.name' => ''],
                ['contacts.0.name' => '田中花子'],
            ],
            'when destination is empty' => [
                ['billingDestination.destination' => ['入力してください。']],
                ['billingDestination.destination' => ''],
                ['billingDestination.destination' => BillingDestination::agent()->value()],
            ],
            'when destination is invalid' => [
                ['billingDestination.destination' => ['請求先を指定してください。']],
                ['billingDestination.destination' => self::INVALID_ENUM_VALUE],
                ['billingDestination.destination' => BillingDestination::agent()->value()],
            ],
            'when paymentMethod is empty' => [
                ['billingDestination.paymentMethod' => ['入力してください。']],
                ['billingDestination.paymentMethod' => ''],
                ['billingDestination.paymentMethod' => PaymentMethod::withdrawal()->value()],
            ],
            'when paymentMethod is invalid' => [
                ['billingDestination.paymentMethod' => ['支払方法を指定してください。']],
                ['billingDestination.paymentMethod' => self::INVALID_ENUM_VALUE],
                ['billingDestination.paymentMethod' => PaymentMethod::withdrawal()->value()],
            ],
            'when contractNumber is empty and paymentMethod is withdrawal' => [
                ['billingDestination.contractNumber' => ['入力してください。']],
                [
                    'billingDestination.contractNumber' => '',
                    'billingDestination.paymentMethod' => PaymentMethod::withdrawal()->value(),
                ],
                [
                    'billingDestination.contractNumber' => '0123456789',
                    'billingDestination.paymentMethod' => PaymentMethod::withdrawal()->value(),
                ],
            ],
            'when contractNumber is not string' => [
                ['billingDestination.contractNumber' => ['文字列で入力してください。', '契約者番号は10文字で入力してください。']],
                ['billingDestination.contractNumber' => 123],
                ['billingDestination.contractNumber' => '0123456789'],
            ],
            'when contractNumber is not length of 10' => [
                ['billingDestination.contractNumber' => ['契約者番号は10文字で入力してください。']],
                ['billingDestination.contractNumber' => '123'],
                ['billingDestination.contractNumber' => '0123456789'],
            ],
            'when corporationName is empty and destination is corporation' => [
                ['billingDestination.corporationName' => ['入力してください。']],
                [
                    'billingDestination.corporationName' => '',
                    'billingDestination.destination' => BillingDestination::corporation()->value(),
                ],
                [
                    'billingDestination.corporationName' => 'ユースタイルラボラトリー',
                    'billingDestination.destination' => BillingDestination::corporation()->value(),
                ],
            ],
            'when corporationName is not string' => [
                ['billingDestination.corporationName' => ['文字列で入力してください。']],
                ['billingDestination.corporationName' => 123],
                ['billingDestination.corporationName' => 'ユースタイルラボラトリー'],
            ],
            'when corporationName is longer than 200' => [
                ['billingDestination.corporationName' => ['200文字以内で入力してください。']],
                ['billingDestination.corporationName' => str_repeat('ア', 201)],
                ['billingDestination.corporationName' => 'ユースタイルラボラトリー'],
            ],
            'when agentName is empty and destination is not theirself' => [
                ['billingDestination.agentName' => ['入力してください。']],
                [
                    'billingDestination.agentName' => '',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
                [
                    'billingDestination.agentName' => 'ユースタイルラボラトリー',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
            ],
            'when agentName is not string' => [
                ['billingDestination.agentName' => ['文字列で入力してください。']],
                ['billingDestination.agentName' => 123],
                ['billingDestination.agentName' => 'ユースタイルラボラトリー'],
            ],
            'when agentName is longer than 100' => [
                ['billingDestination.agentName' => ['100文字以内で入力してください。']],
                ['billingDestination.agentName' => str_repeat('ア', 101)],
                ['billingDestination.agentName' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.postcode is empty and destination is not theirself' => [
                ['billingDestination.postcode' => ['入力してください。']],
                [
                    'billingDestination.postcode' => '',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
                [
                    'billingDestination.postcode' => '123-4567',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
            ],
            'when billingDestination.postcode is invalid' => [
                ['billingDestination.postcode' => ['郵便番号は7桁で入力してください。']],
                ['billingDestination.postcode' => '123-456'],
                ['billingDestination.postcode' => '123-4567'],
            ],
            'when billingDestination.prefecture is empty and destination is not theirself' => [
                ['billingDestination.prefecture' => ['入力してください。']],
                [
                    'billingDestination.prefecture' => '',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
                [
                    'billingDestination.prefecture' => Prefecture::aichi()->value(),
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
            ],
            'when billingDestination.prefecture is invalid' => [
                ['billingDestination.prefecture' => ['都道府県を指定してください。']],
                ['billingDestination.prefecture' => self::INVALID_ENUM_VALUE],
                ['billingDestination.prefecture' => Prefecture::aichi()->value()],
            ],
            'when billingDestination.city is empty and destination is not theirself' => [
                ['billingDestination.city' => ['入力してください。']],
                [
                    'billingDestination.city' => '',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
                [
                    'billingDestination.city' => 'ユースタイルラボラトリー',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
            ],
            'when billingDestination.city is not string' => [
                ['billingDestination.city' => ['文字列で入力してください。']],
                ['billingDestination.city' => 123],
                ['billingDestination.city' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.city is longer than 200' => [
                ['billingDestination.city' => ['200文字以内で入力してください。']],
                ['billingDestination.city' => str_repeat('ア', 201)],
                ['billingDestination.city' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.street is empty and destination is not theirself' => [
                ['billingDestination.street' => ['入力してください。']],
                [
                    'billingDestination.street' => '',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
                [
                    'billingDestination.street' => 'ユースタイルラボラトリー',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
            ],
            'when billingDestination.street is not string' => [
                ['billingDestination.street' => ['文字列で入力してください。']],
                ['billingDestination.street' => 123],
                ['billingDestination.street' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.street is longer than 200' => [
                ['billingDestination.street' => ['200文字以内で入力してください。']],
                ['billingDestination.street' => str_repeat('ア', 201)],
                ['billingDestination.street' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.apartment is not string' => [
                ['billingDestination.apartment' => ['文字列で入力してください。']],
                ['billingDestination.apartment' => 123],
                ['billingDestination.apartment' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.apartment is longer than 200' => [
                ['billingDestination.apartment' => ['200文字以内で入力してください。']],
                ['billingDestination.apartment' => str_repeat('ア', 201)],
                ['billingDestination.apartment' => 'ユースタイルラボラトリー'],
            ],
            'when billingDestination.tel is empty and destination is not theirself' => [
                ['billingDestination.tel' => ['入力してください。']],
                [
                    'billingDestination.tel' => '',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
                [
                    'billingDestination.tel' => '03-1234-5678',
                    'billingDestination.destination' => BillingDestination::agent()->value(),
                ],
            ],
            'when billingDestination.tel is invalid' => [
                ['billingDestination.tel' => ['正しい値を入力してください。']],
                ['billingDestination.tel' => '0123'],
                ['billingDestination.tel' => '03-1234-5678'],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'familyName' => '新垣',
            'givenName' => '栄作',
            'phoneticFamilyName' => 'シンガキ',
            'phoneticGivenName' => 'エイサク',
            'sex' => Sex::male()->value(),
            'birthday' => '1982-05-09',
            'postcode' => '123-4567',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '和光市',
            'street' => '広沢 XX-XX',
            'apartment' => 'コーポXXX 202号室',
            'location' => Location::create([
                'lat' => 12.345678,
                'lng' => 123.456789,
            ]),
            'contacts' => Seq::fromArray($this->examples->users[0]->contacts)
                ->map(fn (Contact $x): array => [
                    'tel' => $x->tel,
                    'relationship' => $x->relationship->value(),
                    'name' => $x->name,
                ])
                ->toArray(),
            'email' => 'example@mail.com',
            'billingDestination' => [
                'destination' => $this->examples->users[0]->billingDestination->destination->value(),
                'paymentMethod' => $this->examples->users[0]->billingDestination->paymentMethod->value(),
                'contractNumber' => $this->examples->users[0]->billingDestination->contractNumber,
                'corporationName' => $this->examples->users[0]->billingDestination->corporationName,
                'agentName' => $this->examples->users[0]->billingDestination->agentName,
                'postcode' => $this->examples->users[0]->billingDestination->addr->postcode,
                'prefecture' => $this->examples->users[0]->billingDestination->addr->prefecture->value(),
                'city' => $this->examples->users[0]->billingDestination->addr->city,
                'street' => $this->examples->users[0]->billingDestination->addr->street,
                'apartment' => $this->examples->users[0]->billingDestination->addr->apartment,
                'tel' => $this->examples->users[0]->billingDestination->tel,
            ],
        ];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return \Domain\User\User
     */
    private function expectedPayload(array $input): User
    {
        return User::create([
            'name' => new StructuredName(
                familyName: $input['familyName'],
                givenName: $input['givenName'],
                phoneticFamilyName: $input['phoneticFamilyName'],
                phoneticGivenName: $input['phoneticGivenName'],
            ),
            'sex' => Sex::from($input['sex']),
            'birthday' => Carbon::parse($input['birthday']),
            'addr' => new Addr(
                postcode: $input['postcode'],
                prefecture: Prefecture::from($input['prefecture']),
                city: $input['city'],
                street: $input['street'],
                apartment: $input['apartment'] ?? '',
            ),
            'location' => Location::create([
                'lat' => 0,
                'lng' => 0,
            ]),
            'contacts' => Seq::fromArray($input['contacts'])
                ->map(fn (array $x): Contact => Contact::create([
                    'tel' => $x['tel'] ?? '',
                    'relationship' => ContactRelationship::from($x['relationship']),
                    'name' => $x['name'],
                ]))
                ->toArray(),
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::from($input['billingDestination']['destination']),
                'paymentMethod' => PaymentMethod::from($input['billingDestination']['paymentMethod']),
                'contractNumber' => $input['billingDestination']['paymentMethod'] === PaymentMethod::withdrawal()->value()
                    ? $input['billingDestination']['contractNumber']
                    : '',
                'corporationName' => $input['billingDestination']['destination'] === BillingDestination::corporation()->value()
                    ? $input['billingDestination']['corporationName']
                    : '',
                'agentName' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? $input['billingDestination']['agentName']
                    : '',
                'addr' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? new Addr(
                        postcode: $input['billingDestination']['postcode'],
                        prefecture: Prefecture::from($input['billingDestination']['prefecture']),
                        city: $input['billingDestination']['city'],
                        street: $input['billingDestination']['street'],
                        apartment: $input['billingDestination']['apartment'] ?? '',
                    )
                    : null,
                'tel' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? $input['billingDestination']['tel']
                    : '',
            ]),
            'isEnabled' => true,
        ]);
    }
}

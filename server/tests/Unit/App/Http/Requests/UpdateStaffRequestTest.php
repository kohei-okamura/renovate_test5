<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Role\Role;
use Domain\Staff\Certification;
use Domain\Staff\StaffStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * UpdateStaffRequest のテスト
 */
final class UpdateStaffRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use LookupRoleUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected UpdateStaffRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateStaffRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );

            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, int ...$ids) use ($self): Seq {
                    return Seq::fromArray($self->examples->roles)
                        ->filter(fn (Role $x): bool => in_array($x->id, $ids, true));
                })
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, array $permission, int ...$ids) use ($self): Seq {
                    return Seq::fromArray($self->examples->offices)
                        ->filter(fn (Office $x): bool => in_array($x->id, $ids, true));
                });
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->staffs[1]->email)
                ->andReturn(Option::some($self->examples->staffs[1]));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return array for upload', function (): void {
            $input = $this->defaultInput();
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
            $expected = $this->expectedPayload($input);
            $payload = $this->request->payload();

            $this->assertEquals($expected, $payload);
        });
        $this->should(
            'return Staff when no-required param is null or empty',
            function (string $key): void {
                foreach (['', null] as $value) {
                    $input = $this->defaultInput();
                    Arr::set($input, $key, $value);
                    $this->request->initialize(
                        [],
                        [],
                        [],
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json'],
                        Json::encode($input),
                    );
                    $this->assertEquals($this->expectedPayload($input), $this->request->payload());
                }
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when fax' => ['fax'],
                    'when employeeNumber' => ['employeeNumber'],
                ],
            ]
        );
        $this->should(
            'return Staff when non-required param is omitted',
            function (string $key): void {
                $input = $this->defaultInput();
                Arr::forget($input, $key);
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input),
                );
                $this->assertEquals($this->expectedPayload($input), $this->request->payload());
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when fax' => ['fax'],
                    'when certifications' => ['certifications'],
                    'when employeeNumber' => ['employeeNumber'],
                    'when roleIds' => ['roleIds'],
                    'when officeIds' => ['officeIds'],
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
            ],
            'when familyName is longer than 100' => [
                ['familyName' => ['100文字以内で入力してください。']],
                ['familyName' => str_repeat('山', 101)],
                ['familyName' => str_repeat('山', 100)],
            ],
            'when givenName is empty' => [
                ['givenName' => ['入力してください。']],
                ['givenName' => ''],
            ],
            'when givenName is longer than 100' => [
                ['givenName' => ['100文字以内で入力してください。']],
                ['givenName' => str_repeat('山', 101)],
                ['givenName' => str_repeat('山', 100)],
            ],
            'when phoneticFamilyName is empty' => [
                ['phoneticFamilyName' => ['入力してください。']],
                ['phoneticFamilyName' => ''],
            ],
            'when phoneticFamilyName is longer than 100' => [
                ['phoneticFamilyName' => ['100文字以内で入力してください。']],
                ['phoneticFamilyName' => str_repeat('ア', 101)],
                ['phoneticFamilyName' => str_repeat('ア', 100)],
            ],
            'when phoneticFamilyName contains non-katakana character(s)' => [
                ['phoneticFamilyName' => ['カタカナで入力してください。']],
                ['phoneticFamilyName' => 'ナイトウ勇介'],
                ['phoneticFamilyName' => 'ユウスケ'],
            ],
            'when phoneticGivenName is empty' => [
                ['phoneticGivenName' => ['入力してください。']],
                ['phoneticGivenName' => ''],
            ],
            'when phoneticGivenName is longer than 100' => [
                ['phoneticGivenName' => ['100文字以内で入力してください。']],
                ['phoneticGivenName' => str_repeat('ア', 101)],
                ['phoneticGivenName' => str_repeat('ア', 100)],
            ],
            'when phoneticGivenName contains non-katakana character(s)' => [
                ['phoneticGivenName' => ['カタカナで入力してください。']],
                ['phoneticGivenName' => '内藤ユウスケ'],
                ['phoneticGivenName' => 'ユウスケ'],
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
            ],
            'when invalid birthday given' => [
                ['birthday' => ['正しい日付を入力してください。']],
                ['birthday' => '1999-02-29'],
                ['birthday' => '2000-02-29'],
            ],
            'when postcode is empty' => [
                ['postcode' => ['入力してください。']],
                ['postcode' => ''],
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
            'when tel is empty' => [
                ['tel' => ['入力してください。']],
                ['tel' => ''],
                ['tel' => '012-345-6789'],
            ],
            'when tel is non phone number' => [
                ['tel' => ['正しい値を入力してください。']],
                ['tel' => '9876-5432-10'],
                ['tel' => '012-345-6789'],
            ],
            'when fax is non phone number' => [
                ['fax' => ['正しい値を入力してください。']],
                ['fax' => '9876-5432-10'],
                ['fax' => '012-345-6789'],
            ],
            'when email is empty' => [
                ['email' => ['入力してください。']],
                ['email' => ''],
            ],
            'when email is longer than 255' => [
                ['email' => ['255文字以内で入力してください。']],
                ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'],
                ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'],
            ],
            'when email is already used' => [
                ['email' => ['このメールアドレスはすでに使用されています。']],
                ['email' => $this->examples->staffs[1]->email],
                ['email' => $this->examples->staffs[0]->email],
            ],
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => $this->examples->staffs[0]->status->value()],
            ],
            'when status not exists' => [
                ['status' => ['スタッフの状態を指定してください。']],
                ['status' => 'あいうえおかきくけこ'],
                ['status' => $this->examples->staffs[0]->status->value()],
            ],
            'when certifications is invalid' => [
                ['certifications.0' => ['資格を指定してください。']],
                ['certifications' => [self::INVALID_ENUM_VALUE]],
                ['certifications' => []],
            ],
            'when employeeNumber is longer than 20' => [
                ['employeeNumber' => ['20文字以内で入力してください。']],
                ['employeeNumber' => str_repeat('ア', 21)],
                ['employeeNumber' => str_repeat('ア', 20)],
            ],
            'when roleIds are includes non-exist id' => [
                ['roleIds' => ['正しい値を入力してください。']],
                ['roleIds' => [$this->examples->roles[0]->id, self::NOT_EXISTING_ID]],
                ['roleIds' => []],
            ],
            'when officeIds are includes non-exist id' => [
                ['officeIds' => ['正しい値を入力してください。']],
                ['officeIds' => [self::NOT_EXISTING_ID, $this->examples->offices[0]->id]],
                ['officeIds' => []],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * payload が返す配列.
     *
     * @param array $input
     * @return array
     */
    public function expectedPayload(array $input): array
    {
        return [
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
            'tel' => $input['tel'],
            'fax' => $input['fax'] ?? '',
            'email' => $input['email'],
            'status' => StaffStatus::from($input['status']),
            'certifications' => isset($input['certifications']) ? Seq::fromArray($input['certifications'])
                ->map(fn (int $x): Certification => Certification::from($x)) : [],
            'employeeNumber' => $input['employeeNumber'] ?? '',
            'roleIds' => $input['roleIds'] ?? [],
            'officeIds' => $input['officeIds'] ?? [],
            'officeGroupIds' => $input['officeGroupIds'] ?? [],
        ];
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
            'postcode' => '351-0106',
            'prefecture' => Prefecture::saitama()->value(),
            'city' => '和光市',
            'street' => '広沢2-10',
            'apartment' => 'クラブコート和光',
            'location' => [
                'lat' => 12.345678,
                'lng' => 123.456789,
            ],
            'tel' => '048-111-2222',
            'fax' => '03-6666-7777',
            'email' => 'example@mail.com',
            'password' => 'passworddddddd',
            'status' => StaffStatus::active()->value(),
            'certifications' => [Certification::acupuncturist()->value()],
            'employeeNumber' => '0123456789',
            'roleIds' => [$this->examples->roles[0]->id],
            'officeIds' => [$this->examples->offices[1]->id, $this->examples->offices[2]->id],
            // ルートパラメーター
            'id' => $this->examples->staffs[0]->id,
        ];
    }
}

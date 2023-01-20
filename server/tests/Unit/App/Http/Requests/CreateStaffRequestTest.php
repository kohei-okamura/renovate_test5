<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\OrganizationRequest;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Context\Context;
use Domain\Staff\Certification;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CreateStaffRequest のテスト
 */
final class CreateStaffRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupInvitationUseCaseMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected Prefecture $prefecture;
    protected CreateStaffRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]))
                ->byDefault();
            $self->lookupInvitationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->invitations[0]))
                ->byDefault();
            $self->lookupInvitationUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->invitations[1]->id)
                ->andReturn(Seq::from($self->examples->invitations[0]->copy(['email' => 'conflict@example.com'])))
                ->byDefault();
            $self->lookupInvitationUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), 'conflict@example.com')
                ->andReturn(Option::some($self->examples->staffs[0]))
                ->byDefault();
            $self->request = new CreateStaffRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('return Staff', function (): void {
            $input = $this->defaultInput();
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input),
            );
            $this->assertModelStrictEquals($this->expectedPayload($input), $this->request->payload());
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
                    $this->assertModelStrictEquals($this->expectedPayload($input), $this->request->payload());
                }
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when fax' => ['fax'],
                    'when status' => ['status'],
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
                $this->assertModelStrictEquals($this->expectedPayload($input), $this->request->payload());
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when fax' => ['fax'],
                    'when status' => ['status'],
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
        $this->should('succeed', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when password is empty' => [
                ['password' => ['入力してください。']],
                ['password' => ''],
            ],
            'when password is shorter than 8' => [
                ['password' => ['8文字以上で入力してください。']],
                ['password' => str_repeat('a', 7)],
                ['password' => str_repeat('a', 8)],
            ],
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
            'when status not exists' => [
                ['status' => ['スタッフの状態を指定してください。']],
                ['status' => 'あいうえおかきくけこ'],
                ['status' => $this->examples->staffs[0]->status->value()],
            ],
            'when certification is invalid' => [
                ['certifications.0' => ['資格を指定してください。']],
                ['certifications' => [self::INVALID_ENUM_VALUE]],
                ['certifications' => [Certification::careManager()->value()]],
            ],
            'when invitationId is empty' => [
                ['invitationId' => ['入力してください。']],
                ['invitationId' => ''],
                ['invitationId' => $this->examples->invitations[0]->id],
            ],
            'when unknown invitationId given' => [
                ['invitationId' => ['正しい値を入力してください。'], 'token' => ['無効なトークンです。']],
                ['invitationId' => self::NOT_EXISTING_ID],
                ['invitationId' => $this->examples->invitations[0]->id],
            ],
            'when the email of the invitation with the invitationId is already used' => [
                ['invitationId' => ['このメールアドレスはすでに使用されているため、登録できません。']],
                ['invitationId' => $this->examples->invitations[1]->id],
                ['invitationId' => $this->examples->invitations[0]->id],
            ],
            'when token is empty' => [
                ['token' => ['入力してください。']],
                ['token' => ''],
                ['token' => $this->examples->invitations[0]->token],
            ],
            'when unknown token given' => [
                ['token' => ['無効なトークンです。']],
                ['token' => self::NOT_EXISTING_TOKEN],
                ['token' => $this->examples->invitations[0]->token],
            ],
        ];
        $this->should(
            'fail',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
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
            'password' => 'password',
            'familyName' => '内藤',
            'givenName' => '勇介',
            'phoneticFamilyName' => 'ナイトウ',
            'phoneticGivenName' => 'ユウスケ',
            'sex' => Sex::male()->value(),
            'birthday' => '1985-02-24',
            'postcode' => '123-4567',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '江戸川区',
            'street' => '北小岩2丁目 XX-XX',
            'apartment' => 'コーポXXX 202号室',
            'tel' => '03-1234-5678',
            'status' => StaffStatus::active()->value(),
            'certifications' => [
                Certification::acupuncturist()->value(),
                Certification::careManager()->value(),
            ],
            'invitationId' => $this->examples->invitations[0]->id,
            'token' => $this->examples->invitations[0]->token,
        ];
    }

    /**
     * payload() が返す期待値の組み立て.
     *
     * @param array $input
     * @return \Domain\Staff\Staff
     */
    private function expectedPayload(array $input): Staff
    {
        return Staff::create(
            [
                'employeeNumber' => '',
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
                    'lat' => null,
                    'lng' => null,
                ]),
                'fax' => $input['fax'] ?? '',
                'password' => Password::fromString($input['password']),
                'certifications' => Seq::fromArray($input['certifications'])
                    ->map(fn (int $x): Certification => Certification::from($x))
                    ->toArray(),
                'isVerified' => true,
                'status' => empty($input['status']) ? StaffStatus::active() : StaffStatus::from($input['status']),
            ] + $input
        );
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateInvitationRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Staff\Invitation;
use Faker\Generator;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateInvitationRequest} のテスト.
 */
class CreateInvitationRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeGroupUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupRoleUseCaseMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected CreateInvitationRequest $request;

    private Generator $faker;
    private Invitation $invitation;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateInvitationRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->request = new CreateInvitationRequest();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createStaffs(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createStaffs(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();
            $self->lookupRoleUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createStaffs(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->staffs[0]->email)
                ->andReturn(Option::from($self->examples->staffs[0]));
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->invitation = $self->examples->invitations[0];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return Invitation', function (): void {
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
            $this->assertEquals(
                $this->expectedPayload($this->defaultInput()),
                $this->request->payload()
            );
        });
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
            'when emails is empty' => [
                ['emails' => ['入力してください。']],
                ['emails' => []],
                ['emails' => [$this->invitation->email]],
            ],
            'when emails is duplicated valid' => [
                ['emails.0' => ['それぞれ別の値を入力してください。'], 'emails.1' => ['それぞれ別の値を入力してください。']],
                ['emails' => ['aaa@example.com', 'aaa@example.com']],
                ['emails' => [$this->invitation->email]],
            ],
            'when emails is longer than 255' => [
                ['emails.0' => ['255文字以内で入力してください。']],
                ['emails.0' => str_repeat('a', 256) . '@example.com'],
                ['emails.0' => $this->invitation->email],
            ],
            'when emails is not valid' => [
                ['emails.0' => ['正しいメールアドレスで入力してください。']],
                ['emails.0' => 'abcdefg'],
                ['emails.0' => $this->invitation->email],
            ],
            'when emails is already used' => [
                ['emails.0' => ['このメールアドレスはすでに使用されています。']],
                ['emails.0' => $this->examples->staffs[0]->email],
            ],
            'when roleIds is empty' => [
                ['roleIds' => ['入力してください。']],
                ['roleIds' => []],
                ['roleIds' => $this->invitation->roleIds],
            ],
            'when roleIds contains not existing id' => [
                ['roleIds' => ['正しい値を入力してください。']],
                ['roleIds' => [$this->examples->roles[0]->id, self::NOT_EXISTING_ID]],
                ['roleIds' => $this->invitation->roleIds],
            ],
            'when officeIds is empty' => [
                ['officeIds' => ['入力してください。']],
                ['officeIds' => []],
                ['officeIds' => $this->invitation->officeIds],
            ],
            'when officeIds contains not existing id' => [
                ['officeIds' => ['正しい値を入力してください。']],
                ['officeIds' => [self::NOT_EXISTING_ID, $this->examples->offices[0]->id]],
                ['officeIds' => $this->invitation->officeIds],
            ],
            'when officeGroupIds contains not existing id' => [
                ['officeGroupIds' => ['正しい値を入力してください。']],
                ['officeGroupIds' => [self::NOT_EXISTING_ID, $this->examples->officeGroups[0]->id]],
                ['officeGroupIds' => $this->invitation->officeGroupIds],
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
            'emails' => [$this->invitation->email],
            'officeIds' => $this->invitation->officeIds,
            'officeGroupIds' => $this->invitation->officeGroupIds,
            'roleIds' => $this->invitation->roleIds,
        ];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return \ScalikePHP\Seq
     */
    private function expectedPayload(array $input): Seq
    {
        return Seq::fromArray($input['emails'])->map(fn (string $x): Invitation => Invitation::create([
            'email' => $x,
            'officeIds' => $input['officeIds'],
            'officeGroupIds' => $input['officeGroupIds'],
            'roleIds' => $input['roleIds'],
        ]))->computed();
    }
}

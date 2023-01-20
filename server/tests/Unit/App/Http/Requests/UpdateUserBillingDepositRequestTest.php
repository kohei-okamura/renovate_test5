<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserBillingDepositRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Faker\Generator;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateUserBillingDepositRequest} のテスト.
 */
class UpdateUserBillingDepositRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;

    protected UpdateUserBillingDepositRequest $request;

    private Generator $faker;
    private UserBilling $userBilling;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateUserBillingDepositRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->request = new UpdateUserBillingDepositRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), $self->examples->userBillings[0]->id)
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), $self->examples->userBillings[4]->id)
                ->andReturn(Seq::from($self->examples->userBillings[4]))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->userBilling = $self->examples->userBillings[0];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return organizationSetting', function (): void {
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
            $expected = [
                'ids' => [$this->examples->userBillings[4]->id],
                'depositedAt' => Carbon::now(),
            ];
            $this->assertEquals(
                $expected,
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
            'when ids is empty ' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => [$this->examples->userBillings[4]->id]],
            ],
            'when ids is not array ' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => [$this->examples->userBillings[4]->id]],
            ],
            'when ids can not update' => [
                ['ids' => ['入金日を更新できない利用者請求が含まれています。']],
                ['ids' => [$this->examples->userBillings[0]->id]],
                ['ids' => [$this->examples->userBillings[4]->id]],
            ],
            'when depositedOn is empty ' => [
                ['depositedOn' => ['入力してください。']],
                ['depositedOn' => ''],
                ['depositedOn' => Carbon::now()->toString()],
            ],
            'when depositedOn is not date ' => [
                ['depositedOn' => ['正しい日付を入力してください。']],
                ['depositedOn' => 'あいうえお'],
                ['depositedOn' => Carbon::now()->toString()],
            ],
            'when depositedOn is after tomorrow ' => [
                ['depositedOn' => [Carbon::tomorrow()->toDateString() . 'より前の日付を入力してください。']],
                ['depositedOn' => Carbon::tomorrow()->toString()],
                ['depositedOn' => Carbon::now()->toString()],
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
            'ids' => [$this->examples->userBillings[4]->id],
            'depositedOn' => Carbon::now()->toString(),
        ];
    }
}

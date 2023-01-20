<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateWithdrawalTransactionRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBillingResult;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateWithdrawalTransactionRequest} のテスト.
 */
final class CreateWithdrawalTransactionRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected CreateWithdrawalTransactionRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new CreateWithdrawalTransactionRequest();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->userBillings[0]->copy([
                        'user' => $self->examples->userBillings[0]->user->copy([
                            'billingDestination' => $self->examples->userBillings[0]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ]),
                    $self->examples->userBillings[1]->copy([
                        'user' => $self->examples->userBillings[1]->user->copy([
                            'billingDestination' => $self->examples->userBillings[1]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ])
                ))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createWithdrawalTransactions(), $self->examples->userBillings[2]->id)
                ->andReturn(Seq::from(
                    $self->examples->userBillings[2]->copy([
                        'user' => $self->examples->userBillings[2]->user->copy([
                            'billingDestination' => $self->examples->userBillings[2]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::transfer(),
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ]),
                ))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createWithdrawalTransactions(), $self->examples->userBillings[3]->id)
                ->andReturn(Seq::from(
                    $self->examples->userBillings[3]->copy([
                        'user' => $self->examples->userBillings[3]->user->copy([
                            'billingDestination' => $self->examples->userBillings[3]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'result' => UserBillingResult::inProgress(),
                    ]),
                ))
                ->byDefault();

            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createWithdrawalTransactions(), $self->examples->userBillings[4]->id)
                ->andReturn(Seq::from(
                    $self->examples->userBillings[4]->copy([
                        'user' => $self->examples->userBillings[3]->user->copy([
                            'billingDestination' => $self->examples->userBillings[3]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'dwsItem' => $self->examples->userBillings[4]->dwsItem->copy([
                            'totalAmount' => 0,
                        ]),
                        'ltcsItem' => null,
                        'otherItems' => [],
                        'carriedOverAmount' => 0,
                        'result' => UserBillingResult::pending(),
                    ]),
                ))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createWithdrawalTransactions(),
                    $self->examples->userBillings[5]->id,
                    $self->examples->userBillings[6]->id,
                )
                ->andReturn(Seq::from(
                    $self->examples->userBillings[0]->copy([
                        'user' => $self->examples->userBillings[0]->user->copy([
                            'billingDestination' => $self->examples->userBillings[0]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                            'bankAccount' => $self->examples->userBillings[0]->user->bankAccount->copy([
                                'bankCode' => '0005',
                                'bankAccountNumber' => '252525',
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ]),
                    $self->examples->userBillings[1]->copy([
                        'user' => $self->examples->userBillings[1]->user->copy([
                            'billingDestination' => $self->examples->userBillings[1]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ])
                ))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createWithdrawalTransactions(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

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
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes(), $validator->errors()->toJson());
        });
        $examples = [
            'when userBillingIds is empty' => [
                ['userBillingIds' => ['入力してください。']],
                ['userBillingIds' => []],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
            'when userBillingIds is not array' => [
                ['userBillingIds' => ['配列にしてください。', '正しい値を入力してください。']],
                ['userBillingIds' => 'error'],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
            'when unknown userBillingIds contain' => [
                ['userBillingIds' => ['正しい値を入力してください。']],
                ['userBillingIds' => [self::NOT_EXISTING_ID]],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
            'when paymentMethods of UserBillings with userBillingIds contain other than withdrawal' => [
                ['userBillingIds' => ['支払方法が口座振替でない利用者請求IDが含まれています。']],
                ['userBillingIds' => [$this->examples->userBillings[2]->id]],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
            'when result of UserBillings with userBillingIds contain other than pending' => [
                ['userBillingIds' => ['全銀ファイル作成済みの利用者請求IDが含まれています。']],
                ['userBillingIds' => [$this->examples->userBillings[3]->id]],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
            'when UserBillings with userBillingIds contain invalid bankAccount' => [
                ['userBillingIds' => ['口座番号に問題のある利用者請求が含まれているため全銀ファイルを作成できません。']],
                ['userBillingIds' => [$this->examples->userBillings[5]->id, $this->examples->userBillings[6]->id]],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($input);
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
        return ['userBillingIds' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id]];
    }
}

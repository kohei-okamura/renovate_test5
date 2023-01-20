<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserBillingRequest;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingResult;
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
 * {@link \App\Http\Requests\UpdateUserBillingRequest} のテスト.
 */
class UpdateUserBillingRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private UpdateUserBillingRequest $request;
    private UserBilling $userBilling;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateUserBillingRequestTest $self): void {
            $self->request = new UpdateUserBillingRequest();
            $self->userBilling = $self->examples->userBillings[0];

            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->replacePaymentMethod($self->examples->userBillings[0], PaymentMethod::withdrawal())))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), $self->examples->userBillings[1]->id)
                ->andReturn(Seq::from($self->examples->userBillings[1]->copy(['transactedAt' => Carbon::now()])));
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), $self->examples->userBillings[2]->id)
                ->andReturn(Seq::from($self->replacePaymentMethod($self->examples->userBillings[2], PaymentMethod::transfer())));

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
        $this->should('return assoc of UserBilling for update', function (): void {
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
                $this->expectedPayload(),
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
        $withdrawal = PaymentMethod::withdrawal()->value();
        $examples = [
            'when a carriedOverAmount is empty' => [
                ['carriedOverAmount' => ['入力してください。']],
                ['carriedOverAmount' => ''],
                ['carriedOverAmount' => $this->userBilling->carriedOverAmount],
            ],
            'when a carriedOverAmount is not integer' => [
                ['carriedOverAmount' => ['整数で入力してください。']],
                ['carriedOverAmount' => 123.4],
                ['carriedOverAmount' => $this->userBilling->carriedOverAmount],
            ],
            'when the user billing amount is less than 0' => [
                ['carriedOverAmount' => ['請求金額が0円以上となるように入力してください。']],
                ['carriedOverAmount' => -50000],
                ['carriedOverAmount' => $this->userBilling->carriedOverAmount],
            ],
            'when a paymentMethod is empty' => [
                ['paymentMethod' => ['入力してください。']],
                ['paymentMethod' => ''],
            ],
            'when a paymentMethod is invalid' => [
                ['paymentMethod' => ['支払方法を指定してください。']],
                ['paymentMethod' => self::INVALID_ENUM_VALUE],
            ],
            'when a paymentMethod is withdrawal' => [
                ['paymentMethod' => ['変更できない支払方法が指定されました。']],
                ['id' => $this->examples->userBillings[2]->id, 'paymentMethod' => $withdrawal],
            ],
            'when the UserBilling with the id cannot be updated' => [
                ['id' => ['利用者請求を更新できません。']],
                ['id' => $this->examples->userBillings[1]->id],
                ['id' => $this->examples->userBillings[0]->id],
            ],
            'when bankName is empty' => [
                ['bankAccount.bankName' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankName' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankName' => '三菱東京UFJ銀行'],
            ],
            'when bankName is longer than 100' => [
                ['bankAccount.bankName' => ['100文字以内で入力してください。']],
                ['bankAccount.bankName' => str_repeat('あ', 101)],
                ['bankAccount.bankName' => str_repeat('あ', 100)],
            ],
            'when bankCode is empty' => [
                ['bankAccount.bankCode' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankCode' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankCode' => '1234'],
            ],
            'when bankCode is longer than 4' => [
                ['bankAccount.bankCode' => ['4桁で入力してください。']],
                ['bankAccount.bankCode' => '1234567890'],
                ['bankAccount.bankCode' => '1234'],
            ],
            'when bankBranchName is empty' => [
                ['bankAccount.bankBranchName' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankBranchName' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankBranchName' => '中野坂上支店'],
            ],
            'when bankBranchName is longer than 100' => [
                ['bankAccount.bankBranchName' => ['100文字以内で入力してください。']],
                ['bankAccount.bankBranchName' => str_repeat('あ', 101)],
                ['bankAccount.bankBranchName' => '中野坂上支店'],
            ],
            'when bankBranchCode is empty' => [
                ['bankAccount.bankBranchCode' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankBranchCode' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankBranchCode' => '567'],
            ],
            'when bankBranchCode is longer than 3' => [
                ['bankAccount.bankBranchCode' => ['3桁で入力してください。']],
                ['bankAccount.bankBranchCode' => '1234567890'],
                ['bankAccount.bankBranchCode' => '567'],
            ],
            'when bankAccountType is empty' => [
                ['bankAccount.bankAccountType' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankAccountType' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankAccountType' => 1],
            ],
            'when unknown bankAccountType given' => [
                ['bankAccount.bankAccountType' => ['銀行口座種別を選択してください。']],
                ['bankAccount.bankAccountType' => 999],
                ['bankAccount.bankAccountType' => BankAccountType::ordinaryDeposit()->value()],
            ],
            'when bankAccountNumber is empty' => [
                ['bankAccount.bankAccountNumber' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankAccountNumber' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankAccountNumber' => '1234567'],
            ],
            'when bankCode is the JP bank and bankAccountNumber is not 8 digits' => [
                ['bankAccount.bankAccountNumber' => ['ゆうちょ銀行の場合は8桁、それ以外の場合は7桁になるように入力してください。']],
                ['bankAccount.bankCode' => '9900', 'bankAccount.bankAccountNumber' => '7654321'],
                ['bankAccount.bankCode' => '9900', 'bankAccount.bankAccountNumber' => '87654321'],
            ],
            'when bankCode is not the JP bank and bankAccountNumber is not 7 digits' => [
                ['bankAccount.bankAccountNumber' => ['ゆうちょ銀行の場合は8桁、それ以外の場合は7桁になるように入力してください。']],
                ['bankAccount.bankCode' => '0005', 'bankAccount.bankAccountNumber' => '12345678'],
                ['bankAccount.bankCode' => '0005', 'bankAccount.bankAccountNumber' => '1234567'],
            ],
            'when bankAccountHolder is empty' => [
                ['bankAccount.bankAccountHolder' => ['入力してください。']],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankAccountHolder' => ''],
                ['paymentMethod' => $withdrawal, 'bankAccount.bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ'],
            ],
            'when bankAccountHolder is longer than 100' => [
                ['bankAccount.bankAccountHolder' => ['100文字以内で入力してください。']],
                ['bankAccount.bankAccountHolder' => str_repeat('ア', 101)],
                ['bankAccount.bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ'],
            ],
            'when bankAccountHolder is not acceptable characters' => [
                ['bankAccount.bankAccountHolder' => ['口座名義に使用できない文字が含まれています。口座名義に間違いがないかご確認ください。']],
                ['bankAccount.bankAccountHolder' => '[]{}'],
                ['bankAccount.bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ'],
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
            'id' => $this->examples->userBillings[0]->id,
            'carriedOverAmount' => -10,
            'paymentMethod' => PaymentMethod::collection()->value(),
            'bankAccount' => [
                'bankName' => '三菱東京UFJ銀行',
                'bankCode' => '1234',
                'bankBranchName' => '中野坂上支店',
                'bankBranchCode' => '567',
                'bankAccountType' => BankAccountType::ordinaryDeposit()->value(),
                'bankAccountNumber' => '1234567',
                'bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ',
            ],
        ];
    }

    /**
     * payload が返す連想配列.
     *
     * @return array
     */
    private function expectedPayload(): array
    {
        $input = $this->defaultInput();
        $bankAccount = $input['bankAccount'];
        return [
            'carriedOverAmount' => $input['carriedOverAmount'],
            'paymentMethod' => PaymentMethod::from($input['paymentMethod']),
            'bankAccount' => UserBillingBankAccount::create([
                ...$bankAccount,
                'bankAccountType' => BankAccountType::from($bankAccount['bankAccountType']),
            ]),
        ];
    }

    /**
     * 支払方法を置き換える.
     *
     * @param \Domain\UserBilling\UserBilling $billing
     * @param \Domain\User\PaymentMethod $paymentMethod
     * @return \Domain\UserBilling\UserBilling
     */
    private static function replacePaymentMethod(UserBilling $billing, PaymentMethod $paymentMethod): UserBilling
    {
        return $billing->copy(
            [
                'result' => UserBillingResult::pending(),
                'transactedAt' => null,
                'user' => $billing->user->copy([
                    'billingDestination' => $billing->user->billingDestination->copy([
                        'paymentMethod' => $paymentMethod,
                    ]),
                ]),
            ]
        );
    }
}

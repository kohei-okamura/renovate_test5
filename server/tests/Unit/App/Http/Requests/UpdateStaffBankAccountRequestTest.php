<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateStaffBankAccountRequest;
use Domain\BankAccount\BankAccountType;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupBankAccountUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * UpdateStaffBankAccountRequest のテスト
 */
class UpdateStaffBankAccountRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupBankAccountUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    protected UpdateStaffBankAccountRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateStaffBankAccountRequestTest $self): void {
            $self->request = new UpdateStaffBankAccountRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return array for update', function (): void {
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
        $examples = [
            'when bankName is empty' => [
                ['bankName' => ['入力してください。']],
                ['bankName' => ''],
                ['bankName' => '三菱東京UFJ銀行'],
            ],
            'when bankName is longer than 100' => [
                ['bankName' => ['100文字以内で入力してください。']],
                ['bankName' => str_repeat('あ', 101)],
                ['bankName' => str_repeat('あ', 100)],
            ],
            'when bankCode is empty' => [
                ['bankCode' => ['入力してください。']],
                ['bankCode' => ''],
                ['bankCode' => '1234'],
            ],
            'when bankCode is longer than 4' => [
                ['bankCode' => ['4桁で入力してください。']],
                ['bankCode' => '1234567890'],
                ['bankCode' => '1234'],
            ],
            'when bankBranchName is empty' => [
                ['bankBranchName' => ['入力してください。']],
                ['bankBranchName' => ''],
                ['bankBranchName' => '中野坂上支店'],
            ],
            'when bankBranchName is longer than 100' => [
                ['bankBranchName' => ['100文字以内で入力してください。']],
                ['bankBranchName' => str_repeat('あ', 101)],
                ['bankBranchName' => '中野坂上支店'],
            ],
            'when bankBranchCode is empty' => [
                ['bankBranchCode' => ['入力してください。']],
                ['bankBranchCode' => ''],
                ['bankBranchCode' => '567'],
            ],
            'when bankBranchCode is longer than 3' => [
                ['bankBranchCode' => ['3桁で入力してください。']],
                ['bankBranchCode' => '1234567890'],
                ['bankBranchCode' => '567'],
            ],
            'when bankAccountType is empty' => [
                ['bankAccountType' => ['入力してください。']],
                ['bankAccountType' => ''],
                ['bankAccountType' => 1],
            ],
            'when unknown bankAccountType given' => [
                ['bankAccountType' => ['銀行口座種別を選択してください。']],
                ['bankAccountType' => 999],
                ['bankAccountType' => BankAccountType::ordinaryDeposit()->value()],
            ],
            'when bankAccountNumber is empty' => [
                ['bankAccountNumber' => ['入力してください。']],
                ['bankAccountNumber' => ''],
                ['bankAccountNumber' => '1234567'],
            ],
            'when bankAccountNumber is longer than 7' => [
                ['bankAccountNumber' => ['7桁で入力してください。']],
                ['bankAccountNumber' => '12345'],
                ['bankAccountNumber' => '1234567'],
            ],
            'when bankAccountHolder is empty' => [
                ['bankAccountHolder' => ['入力してください。']],
                ['bankAccountHolder' => ''],
                ['bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ'],
            ],
            'when bankAccountHolder is longer than 100' => [
                ['bankAccountHolder' => ['100文字以内で入力してください。']],
                ['bankAccountHolder' => str_repeat('ア', 101)],
                ['bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ'],
            ],
            'when bankAccountHolder is not acceptable characters' => [
                ['bankAccountHolder' => ['口座名義に使用できない文字が含まれています。口座名義に間違いがないかご確認ください。']],
                ['bankAccountHolder' => '[]{}'],
                ['bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ'],
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
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'bankName' => '三菱東京UFJ銀行',
            'bankCode' => '1234',
            'bankBranchName' => '中野坂上支店',
            'bankBranchCode' => '567',
            'bankAccountType' => BankAccountType::ordinaryDeposit()->value(),
            'bankAccountNumber' => '1234567',
            'bankAccountHolder' => 'ヤマダ John smith ﾀﾛｳ',
        ];
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function expectedPayload()
    {
        $input = $this->defaultInput();
        return ['bankAccountType' => BankAccountType::from($input['bankAccountType'])] + $input;
    }
}

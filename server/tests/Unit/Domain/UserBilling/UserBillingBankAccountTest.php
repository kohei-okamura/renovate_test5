<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\BankAccount\BankAccountType;
use Domain\UserBilling\UserBillingBankAccount;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingBankAccount} のテスト
 */
class UserBillingBankAccountTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBillingBankAccount $userBillingBankAccount;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserBillingBankAccountTest $self): void {
            $self->values = [
                'bankName' => 'ユースタイル銀行',
                'bankCode' => '1234',
                'bankBranchName' => '丸之内支店',
                'bankBranchCode' => '005',
                'bankAccountType' => BankAccountType::ordinaryDeposit(),
                'bankAccountNumber' => '0123456',
                'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
            ];
            $self->userBillingBankAccount = UserBillingBankAccount::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isSameBankAccount(): void
    {
        $this->should('return true when irrelevant attrs are different', function (): void {
            $bankAccount1 = $this->userBillingBankAccount;
            $bankAccount2 = $this->userBillingBankAccount->copy([
                'bankName' => '異なる銀行',
                'bankBranchName' => '異なる支店',
                'bankAccountType' => BankAccountType::currentDeposit(),
            ]);
            $this->assertTrue($bankAccount1->isSameBankAccount($bankAccount2));
        });
        $this->should(
            'return false',
            function ($overwrite): void {
                $bankAccount1 = $this->userBillingBankAccount;
                $bankAccount2 = $this->userBillingBankAccount->copy($overwrite + [
                    'bankName' => '異なる銀行',
                    'bankBranchName' => '異なる支店',
                    'bankAccountType' => BankAccountType::currentDeposit(),
                ]);
                $this->assertFalse($bankAccount1->isSameBankAccount($bankAccount2));
            },
            [
                'examples' => [
                    'when bankCode is different' => [['bankCode' => '5678']],
                    'when bankBranchCode is different' => [['bankBranchCode' => '987']],
                    'when bankAccountNumber is different' => [['bankAccountNumber' => '0987654']],
                    'when bankAccountHolder is different' => [['bankAccountHolder' => 'ｺﾄﾅﾙﾒｲｷﾞ']],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'bankName' => ['bankName'],
            'bankCode' => ['bankCode'],
            'bankBranchName' => ['bankBranchName'],
            'bankBranchCode' => ['bankBranchCode'],
            'bankAccountType' => ['bankAccountType'],
            'bankAccountNumber' => ['bankAccountNumber'],
            'bankAccountHolder' => ['bankAccountHolder'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBillingBankAccount->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->userBillingBankAccount);
        });
    }
}

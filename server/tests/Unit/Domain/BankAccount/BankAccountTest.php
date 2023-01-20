<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\BankAccount;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * BankAccount のテスト
 */
class BankAccountTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected BankAccount $bankAccount;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BankAccountTest $self): void {
            $self->values = [
                'id' => 1,
                'bankName' => 'ユースタイル銀行',
                'bankCode' => '1234',
                'bankBranchName' => '丸之内支店',
                'bankBranchCode' => '005',
                'bankAccountType' => BankAccountType::ordinaryDeposit(),
                'bankAccountNumber' => '0123456',
                'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->bankAccount = BankAccount::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have bankName attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankName'), Arr::get($this->values, 'bankName'));
        });
        $this->should('have bankCode attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankCode'), Arr::get($this->values, 'bankCode'));
        });
        $this->should('have bankBranchName attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankBranchName'), Arr::get($this->values, 'bankBranchName'));
        });
        $this->should('have bankBranchCode attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankBranchCode'), Arr::get($this->values, 'bankBranchCode'));
        });
        $this->should('have bankAccountType attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankAccountType'), Arr::get($this->values, 'bankAccountType'));
        });
        $this->should('have bankAccountNumber attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankAccountNumber'), Arr::get($this->values, 'bankAccountNumber'));
        });
        $this->should('have bankAccountHolder attribute', function (): void {
            $this->assertSame($this->bankAccount->get('bankAccountHolder'), Arr::get($this->values, 'bankAccountHolder'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->bankAccount->get('version'), Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->bankAccount->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->bankAccount->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->bankAccount);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isValidBankAccountNumber(): void
    {
        $this->should('return true if the bankCode is 9900 and bankAccountNumber is 8 digits and ends with 1', function (): void {
            $bankCode = BankAccount::JAPAN_POST_BANK_BANK_CODE;
            $bankAccountNumber = '87654321';
            $this->assertTrue(BankAccount::isValidBankAccountNumber($bankCode, $bankAccountNumber));
        });
        $this->should('return true if the bankCode is not 9900 and bankAccountNumber is 7 digits', function (): void {
            $bankCode = '0005';
            $bankAccountNumber = '7654321';
            $this->assertTrue(BankAccount::isValidBankAccountNumber($bankCode, $bankAccountNumber));
        });
        $this->should('return false if the bankCode is 9900 and bankAccountNumber is not 8 digits', function (): void {
            $bankCode = BankAccount::JAPAN_POST_BANK_BANK_CODE;
            $bankAccountNumber = '987654321';
            $this->assertFalse(BankAccount::isValidBankAccountNumber($bankCode, $bankAccountNumber));
        });
        $this->should('return false if the bankCode is 9900 and bankAccountNumber does not end with 1', function (): void {
            $bankCode = BankAccount::JAPAN_POST_BANK_BANK_CODE;
            $bankAccountNumber = '76543210';
            $this->assertFalse(BankAccount::isValidBankAccountNumber($bankCode, $bankAccountNumber));
        });
        $this->should('return false if the bankCode is not 9900 and bankAccountNumber is not 7 digits', function (): void {
            $bankCode = '0005';
            $bankAccountNumber = '654321';
            $this->assertFalse(BankAccount::isValidBankAccountNumber($bankCode, $bankAccountNumber));
        });
    }
}

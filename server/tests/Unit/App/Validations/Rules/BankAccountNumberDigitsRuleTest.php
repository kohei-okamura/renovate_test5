<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\BankAccount\BankAccount;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\BankAccountNumberDigitsRule} のテスト.
 */
final class BankAccountNumberDigitsRuleTest extends Test
{
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateBankAccountNumberDigits(): void
    {
        $this->should(
            'pass when valid bankCode and bankAccountNumber given',
            function (string $bankCode, string $bankAccountNumber): void {
                $this->assertTrue(
                    $this->buildSpecifiedValidator($bankCode, $bankAccountNumber)->passes()
                );
            },
            [
                'examples' => [
                    'JP Bank' => [BankAccount::JAPAN_POST_BANK_BANK_CODE, '00252521'],
                    'others' => ['0005', '0025251'],
                ],
            ]
        );
        $this->should(
            'fail when invalid bankCode and bankAccountNumber given',
            function (string $bankCode, string $bankAccountNumber): void {
                $this->assertTrue(
                    $this->buildSpecifiedValidator($bankCode, $bankAccountNumber)->fails()
                );
            },
            [
                'examples' => [
                    'JP Bank and less than 8 digits' => [BankAccount::JAPAN_POST_BANK_BANK_CODE, '0025252'],
                    'JP Bank and over 8 digits' => [BankAccount::JAPAN_POST_BANK_BANK_CODE, '002525252'],
                    'JP Bank and ends in other than 1 ' => [BankAccount::JAPAN_POST_BANK_BANK_CODE, '00252525'],
                    'others and less than 7 digits' => ['0005', '252525'],
                    'others and over 7 digits' => ['0005', '00252525'],
                ],
            ]
        );
    }

    /**
     * バリデータを生成する.
     *
     * @param string $bankCode
     * @param string $bankAccountNumber
     * @return CustomValidator
     */
    private function buildSpecifiedValidator(string $bankCode, string $bankAccountNumber): CustomValidator
    {
        return $this->buildCustomValidator(
            ['bankCode' => $bankCode, 'bankAccountNumber' => $bankAccountNumber],
            ['bankAccountNumber' => 'bank_account_number_digits:bankCode'],
        );
    }
}

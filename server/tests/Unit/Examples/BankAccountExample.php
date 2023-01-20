<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Faker\Generator;

/**
 * BankAccount Example.
 *
 * @property-read \Domain\BankAccount\BankAccount[] $bankAccounts
 */
trait BankAccountExample
{
    /**
     * 利用者銀行口座の一覧を生成する.
     *
     * @return \Domain\BankAccount\BankAccount[]
     */
    protected function bankAccounts(): array
    {
        return [
            $this->generateBankAccount([
                'id' => 1,
            ]),
            $this->generateBankAccount([
                'id' => 2,
            ]),
            $this->generateBankAccount([
                'id' => 3,
            ]),
            $this->generateBankAccount([
                'id' => 4,
            ]),
            $this->generateBankAccount([
                'id' => 5,
            ]),
            $this->generateBankAccount([
                'id' => 6,
            ]),
            $this->generateBankAccount([
                'id' => 7,
            ]),
            $this->generateBankAccount([
                'id' => 8,
            ]),
            $this->generateBankAccount([
                'id' => 9,
            ]),
            $this->generateBankAccount([
                'id' => 10,
            ]),
            $this->generateBankAccount([
                'id' => 11,
            ]),
            $this->generateBankAccount([
                'id' => 12,
            ]),
            $this->generateBankAccount([
                'id' => 13,
            ]),
            $this->generateBankAccount([
                'id' => 14,
            ]),
            $this->generateBankAccount([
                'id' => 15,
            ]),
            $this->generateBankAccount([
                'id' => 16,
            ]),
            $this->generateBankAccount([
                'id' => 17,
            ]),
            $this->generateBankAccount([
                'id' => 18,
            ]),
            $this->generateBankAccount([
                'id' => 19,
            ]),
            $this->generateBankAccount([
                'id' => 20,
            ]),
            $this->generateBankAccount([
                'id' => 21,
                'bankName' => '固定銀行',
                'bankCode' => '1234',
                'bankBranchName' => '固定銀行支店',
                'bankBranchCode' => '567',
                'bankAccountType' => BankAccountType::currentDeposit(),
                'bankAccountNumber' => '1234567',
                'bankAccountHolder' => '固定銀行口座',
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
        ];
    }

    /**
     * Generate an example of BankAccount.
     *
     * @param array $overwrites
     * @return \Domain\BankAccount\BankAccount
     */
    protected function generateBankAccount(array $overwrites)
    {
        $faker = app(Generator::class);
        $values = [
            'bankName' => $faker->text(100),
            'bankCode' => $faker->numerify(str_repeat('#', 4)),
            'bankBranchName' => $faker->text(100),
            'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
            'bankAccountType' => $faker->randomElement(BankAccountType::all()),
            'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
            'bankAccountHolder' => $faker->text(100),
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return BankAccount::create($overwrites + $values);
    }
}

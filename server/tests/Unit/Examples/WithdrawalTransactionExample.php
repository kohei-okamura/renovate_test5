<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Faker\Generator;

/**
 * WithdrawalTransaction Example.
 *
 * @property-read WithdrawalTransaction[] $withdrawalTransactions
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\UserBillingExample
 */
trait WithdrawalTransactionExample
{
    /**
     * 口座振替データの一覧を生成する.
     *
     * @return \Domain\UserBilling\WithdrawalTransaction[]
     */
    protected function withdrawalTransactions(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateWithdrawalTransaction([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'createdAt' => Carbon::parse('2020-10-10T12:10:00+0900'),
            ], $faker),
            $this->generateWithdrawalTransaction([
                'id' => 2,
                'organizationId' => $this->organizations[0]->id,
                'createdAt' => Carbon::parse('2020-10-11T20:30:00+0900'),
            ], $faker),
            $this->generateWithdrawalTransaction([
                'id' => 3,
                'organizationId' => $this->organizations[0]->id,
                'createdAt' => Carbon::parse('2020-10-12T00:00:00+0900'),
            ], $faker),
            $this->generateWithdrawalTransaction([
                'id' => 4,
                'organizationId' => $this->organizations[1]->id,
            ], $faker),
            // 全銀ファイルアップロードE2E用
            $this->generateWithdrawalTransaction([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'deductedOn' => Carbon::parse('2022-02-28'),
                'items' => [
                    WithdrawalTransactionItem::create([
                        'userBillingIds' => [$this->userBillings[16]->id],
                        'zenginRecord' => ZenginDataRecord::create([
                            'bankCode' => '0001',
                            'bankBranchCode' => '001',
                            'bankAccountType' => BankAccountType::currentDeposit(),
                            'bankAccountNumber' => '2222222',
                            'bankAccountHolder' => 'ﾌｼﾞｻﾜ ﾊﾅｺ',
                            'amount' => 9300,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000116',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                    ]),
                    WithdrawalTransactionItem::create([
                        'userBillingIds' => [$this->userBillings[17]->id, $this->userBillings[18]->id],
                        'zenginRecord' => ZenginDataRecord::create([
                            'bankCode' => '0002',
                            'bankBranchCode' => '002',
                            'bankAccountType' => BankAccountType::ordinaryDeposit(),
                            'bankAccountNumber' => '3333333',
                            'bankAccountHolder' => 'ﾋﾗｻｷ ﾋﾛﾕｷ',
                            'amount' => 37200,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000115',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                    ]),
                    WithdrawalTransactionItem::create([
                        'userBillingIds' => [$this->userBillings[19]->id, $this->userBillings[20]->id, $this->userBillings[21]->id],
                        'zenginRecord' => ZenginDataRecord::create([
                            'bankCode' => '0003',
                            'bankBranchCode' => '003',
                            'bankAccountType' => BankAccountType::fixedDeposit(),
                            'bankAccountNumber' => '4444444',
                            'bankAccountHolder' => 'ﾄｳﾀﾞ ｱｹﾐ',
                            'amount' => 28600,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000101',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                    ]),
                ],
            ], $faker),
            $this->generateWithdrawalTransaction([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'deductedOn' => Carbon::parse('2012-01-28'),
                'items' => [
                    WithdrawalTransactionItem::create([
                        'userBillingIds' => [$this->userBillings[16]->id],
                        'zenginRecord' => ZenginDataRecord::create([
                            'bankCode' => '0001',
                            'bankBranchCode' => '001',
                            'bankAccountType' => BankAccountType::currentDeposit(),
                            'bankAccountNumber' => '2222222',
                            'bankAccountHolder' => 'ﾌｼﾞｻﾜ ﾊﾅｺ',
                            'amount' => 9300,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000116',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                    ]),
                    WithdrawalTransactionItem::create([
                        'userBillingIds' => [$this->userBillings[17]->id, $this->userBillings[18]->id],
                        'zenginRecord' => ZenginDataRecord::create([
                            'bankCode' => '0002',
                            'bankBranchCode' => '002',
                            'bankAccountType' => BankAccountType::ordinaryDeposit(),
                            'bankAccountNumber' => '3333333',
                            'bankAccountHolder' => 'ﾋﾗｻｷ ﾋﾛﾕｷ',
                            'amount' => 28600,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000101',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                    ]),
                    WithdrawalTransactionItem::create([
                        'userBillingIds' => [$this->userBillings[19]->id, $this->userBillings[20]->id, $this->userBillings[21]->id],
                        'zenginRecord' => ZenginDataRecord::create([
                            'bankCode' => '0003',
                            'bankBranchCode' => '003',
                            'bankAccountType' => BankAccountType::fixedDeposit(),
                            'bankAccountNumber' => '4444444',
                            'bankAccountHolder' => 'ﾄｳﾀﾞ ｱｹﾐ',
                            'amount' => 28600,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000101',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                    ]),
                ],
            ], $faker),
        ];
    }

    /**
     * Generate an example of WithdrawalTransaction.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\UserBilling\WithdrawalTransaction
     */
    protected function generateWithdrawalTransaction(array $overwrites, Generator $faker): WithdrawalTransaction
    {
        $attrs = [
            'organizationId' => $this->organizations[0]->id,
            'items' => [
                WithdrawalTransactionItem::create([
                    'userBillingIds' => [$this->userBillings[2]->id, $this->userBillings[3]->id],
                    'zenginRecord' => ZenginDataRecord::create([
                        'bankCode' => '0005',
                        'bankBranchCode' => '798',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '1234567',
                        'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                        'amount' => 198000,
                        'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                        'clientNumber' => '12345678901234567890',
                        'withdrawalResultCode' => WithdrawalResultCode::done(),
                    ]),
                ]),
                WithdrawalTransactionItem::create([
                    'userBillingIds' => [$this->userBillings[2]->id, $this->userBillings[3]->id],
                    'zenginRecord' => ZenginDataRecord::create([
                        'bankCode' => '0005',
                        'bankBranchCode' => '798',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '1234567',
                        'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                        'amount' => 198000,
                        'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                        'clientNumber' => '12345678901234567890',
                        'withdrawalResultCode' => WithdrawalResultCode::done(),
                    ]),
                ]),
            ],
            'deductedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'downloadedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return WithdrawalTransaction::create($overwrites + $attrs);
    }
}

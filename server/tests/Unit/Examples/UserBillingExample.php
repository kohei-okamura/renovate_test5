<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\BankAccount\BankAccountType;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Decimal;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\UserBillingDestination;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingOtherItem;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\UserBillingUser;
use Domain\UserBilling\WithdrawalResultCode;
use Faker\Generator;

/**
 * UserBilling Example.
 *
 * @property-read UserBilling[] $userBillings
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\DwsBillingStatementExample
 * @mixin \Tests\Unit\Examples\LtcsBillingStatementExample
 */
trait UserBillingExample
{
    /**
     * 利用者請求の一覧を生成する.
     *
     * @return \Domain\UserBilling\UserBilling[]
     */
    protected function userBillings(): array
    {
        $faker = app(Generator::class);
        // 更新可能な利用者請求
        $updatable = $this->generateUserBilling([
            'id' => 3,
            'organizationId' => $this->organizations[0]->id,
            'userId' => $this->users[2]->id,
            'officeId' => $this->offices[1]->id,
            'issuedOn' => Carbon::create(2020, 5, 20),
            'result' => UserBillingResult::pending(),
            'transactedAt' => null,
        ], $faker);
        return [
            $this->generateUserBilling([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[0]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::transfer(),
                        'contractNumber' => '1234567890',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => $faker->text(100),
                        'bankCode' => $faker->numerify(str_repeat('#', 4)),
                        'bankBranchName' => $faker->text(100),
                        'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
                        'bankAccountType' => $faker->randomElement(BankAccountType::all()),
                        'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
                        'bankAccountHolder' => $faker->text(100),
                    ]),
                ]),
                'depositedAt' => Carbon::create(2021, 4, 1),
                'result' => UserBillingResult::inProgress(),
            ], $faker),
            $this->generateUserBilling([
                'id' => 2,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[1]->id,
                'officeId' => $this->offices[0]->id,
                'providedIn' => Carbon::create(2020, 4),
                'issuedOn' => Carbon::create(2020, 5, 10),
                'depositedAt' => null,
                'result' => UserBillingResult::inProgress(),
            ], $faker),
            $updatable,
            $this->generateUserBilling([
                'id' => 4,
                'organizationId' => $this->organizations[1]->id,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[2]->id,
            ], $faker),
            $this->generateUserBilling([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[3]->id,
                'officeId' => $this->offices[0]->id,
                'result' => UserBillingResult::pending(),
                // 支払い方法が集金
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::collection(),
                        'contractNumber' => '2345678912',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => $faker->text(100),
                        'bankCode' => $faker->numerify(str_repeat('#', 4)),
                        'bankBranchName' => $faker->text(100),
                        'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
                        'bankAccountType' => $faker->randomElement(BankAccountType::all()),
                        'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
                        'bankAccountHolder' => $faker->text(100),
                    ]),
                ]),
                'depositedAt' => null,
            ], $faker),
            $this->generateUserBilling([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[3]->id,
                'officeId' => $this->offices[0]->id,
                // 支払い方法が集金
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::collection(),
                        'contractNumber' => '0123456789',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => $faker->text(100),
                        'bankCode' => $faker->numerify(str_repeat('#', 4)),
                        'bankBranchName' => $faker->text(100),
                        'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
                        'bankAccountType' => $faker->randomElement(BankAccountType::all()),
                        'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
                        'bankAccountHolder' => $faker->text(100),
                    ]),
                ]),
            ], $faker),
            $this->generateUserBilling([
                'id' => 7,
                'organizationId' => $this->organizations[1]->id,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[0]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::transfer(),
                        'contractNumber' => '0123456789',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => $faker->text(100),
                        'bankCode' => $faker->numerify(str_repeat('#', 4)),
                        'bankBranchName' => $faker->text(100),
                        'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
                        'bankAccountType' => $faker->randomElement(BankAccountType::all()),
                        'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
                        'bankAccountHolder' => $faker->text(100),
                    ]),
                ]),
            ], $faker),
            $this->generateUserBilling([
                'id' => 8,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[1]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::transfer(),
                        'contractNumber' => '0123456789',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => $faker->text(100),
                        'bankCode' => $faker->numerify(str_repeat('#', 4)),
                        'bankBranchName' => $faker->text(100),
                        'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
                        'bankAccountType' => $faker->randomElement(BankAccountType::all()),
                        'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
                        'bankAccountHolder' => $faker->text(100),
                    ]),
                ]),
            ], $faker),
            $this->generateUserBilling([
                'id' => 9,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[2]->id,
                'officeId' => $this->offices[1]->id,
                'result' => UserBillingResult::unpaid(),
                'transactedAt' => null,
            ], $faker),
            $this->generateUserBilling([
                'id' => 10,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[2]->id,
                'officeId' => $this->offices[1]->id,
                'result' => UserBillingResult::pending(),
                'withdrawalResultCode' => WithdrawalResultCode::other(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),

            // 口座振替データ作成 API の E2E テスト用
            $this->generateUserBilling([
                'id' => 11,
                'organizationId' => $this->organizations[0]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::theirself(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::withdrawal(),
                        'contractNumber' => '0987654321',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => 'ユースタイル銀行',
                        'bankCode' => '1234',
                        'bankBranchName' => '丸之内支店',
                        'bankBranchCode' => '005',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '0123456',
                        'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
                    ]),
                ]),
                'dwsItem' => UserBillingDwsItem::create([
                    'dwsStatementId' => $this->dwsBillingStatements[0]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 500,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'ltcsItem' => null,
                'otherItems' => [],
                'carriedOverAmount' => -0,
                'result' => UserBillingResult::pending(),
            ], $faker),
            // 口座振替データ作成 API の E2E テスト用
            $this->generateUserBilling([
                'id' => 12,
                'organizationId' => $this->organizations[0]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::theirself(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::withdrawal(),
                        'contractNumber' => '0987654321',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => 'ユースタイル銀行',
                        'bankCode' => '1234',
                        'bankBranchName' => '丸之内支店',
                        'bankBranchCode' => '005',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '0123456',
                        'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
                    ]),
                ]),
                'dwsItem' => null,
                'ltcsItem' => UserBillingLtcsItem::create([
                    'ltcsStatementId' => $this->ltcsBillingStatements[5]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 1500,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'otherItems' => [],
                'carriedOverAmount' => 0,
                'result' => UserBillingResult::pending(),
            ], $faker),
            // 口座振替データ作成 API の E2E テスト用
            $this->generateUserBilling([
                'id' => 13,
                'organizationId' => $this->organizations[0]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::theirself(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::withdrawal(),
                        'contractNumber' => '5432109876',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => 'ラボラトリー銀行',
                        'bankCode' => '5678',
                        'bankBranchName' => '中野支店',
                        'bankBranchCode' => '009',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '3456789',
                        'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-DEF().-/',
                    ]),
                ]),
                'dwsItem' => UserBillingDwsItem::create([
                    'dwsStatementId' => $this->dwsBillingStatements[0]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 100,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'ltcsItem' => UserBillingLtcsItem::create([
                    'ltcsStatementId' => $this->ltcsBillingStatements[5]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 200,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'otherItems' => [
                    UserBillingOtherItem::create([
                        'score' => 100,
                        'unitCost' => Decimal::fromInt(10_0000),
                        'subtotalCost' => 1000,
                        'tax' => ConsumptionTaxRate::ten(),
                        'medicalDeductionAmount' => 5000,
                        'totalAmount' => 300,
                        'copayWithoutTax' => 2000,
                        'copayWithTax' => 2200,
                    ]),
                    UserBillingOtherItem::create([
                        'score' => 200,
                        'unitCost' => Decimal::fromInt(20_0000),
                        'subtotalCost' => 2000,
                        'tax' => ConsumptionTaxRate::ten(),
                        'medicalDeductionAmount' => 10000,
                        'totalAmount' => 400,
                        'copayWithoutTax' => 4000,
                        'copayWithTax' => 4400,
                    ]),
                ],
                'carriedOverAmount' => -500,
                'result' => UserBillingResult::pending(),
            ], $faker),
            $this->generateUserBilling([
                'id' => 14,
                'organizationId' => $this->organizations[0]->id,
                'user' => UserBillingUser::create([
                    'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
                    'addr' => $faker->addr,
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::theirself(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::withdrawal(),
                        'contractNumber' => '5432109876',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => 'ラボラトリー銀行',
                        'bankCode' => '5678',
                        'bankBranchName' => '中野支店',
                        'bankBranchCode' => '009',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '3456789',
                        'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-DEF().-/',
                    ]),
                ]),
                'dwsItem' => UserBillingDwsItem::create([
                    'dwsStatementId' => $this->dwsBillingStatements[0]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 500,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'ltcsItem' => null,
                'otherItems' => [],
                'carriedOverAmount' => -2200,
                'result' => UserBillingResult::pending(),
            ], $faker),
            // fakerを利用していないテストデータ
            $this->generateUserBilling([
                'id' => 15,
                'organizationId' => $this->organizations[0]->id,
                'user' => UserBillingUser::create([
                    'name' => new StructuredName(
                        familyName: 'テスト姓',
                        givenName: 'テスト名',
                        phoneticFamilyName: 'テストセイ',
                        phoneticGivenName: 'テストメイ',
                    ),
                    'addr' => new Addr(
                        postcode: '164-0011',
                        prefecture: Prefecture::tokyo(),
                        city: '中野区',
                        street: '中央1-35-6',
                        apartment: 'レッチフィールド中野坂上ビル6F',
                    ),
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::theirself(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::agent(),
                        'paymentMethod' => PaymentMethod::withdrawal(),
                        'contractNumber' => '5432109876',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => new Addr(
                            postcode: '164-0011',
                            prefecture: Prefecture::tokyo(),
                            city: '中野区',
                            street: '中央1-35-6',
                            apartment: 'レッチフィールド中野坂上ビル6F',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => 'ラボラトリー銀行',
                        'bankCode' => '5678',
                        'bankBranchName' => '中野支店',
                        'bankBranchCode' => '009',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '3456789',
                        'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-DEF().-/',
                    ]),
                ]),
                'office' => UserBillingOffice::create([
                    'name' => '事業所テスト',
                    'corporationName' => '事業所テスト',
                    'addr' => new Addr(
                        postcode: '111-1111',
                        prefecture: Prefecture::kagawa(),
                        city: '事業所市',
                        street: '事業所1-2-8-9',
                        apartment: '事業所建物',
                    ),
                    'tel' => '012-245-6789',
                ]),
                'dwsItem' => UserBillingDwsItem::create([
                    'dwsStatementId' => $this->dwsBillingStatements[0]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 500,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'ltcsItem' => null,
                'otherItems' => [],
                'carriedOverAmount' => -500,
                'result' => UserBillingResult::pending(),
            ], $faker),
            $this->generateUserBilling([
                'id' => 16,
                'dwsItem' => null,
                'ltcsItem' => null,
            ], $faker),
            // 全銀ファイルアップロードE2E用
            $this->generateUserBilling([
                'id' => 17,
                'organizationId' => $this->organizations[0]->id,
                'depositedAt' => Carbon::instance($faker->dateTime),
                'result' => UserBillingResult::inProgress(),
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),
            $this->generateUserBilling([
                'id' => 18,
                'organizationId' => $this->organizations[0]->id,
                'depositedAt' => Carbon::instance($faker->dateTime),
                'result' => UserBillingResult::inProgress(),
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),
            $this->generateUserBilling([
                'id' => 19,
                'organizationId' => $this->organizations[0]->id,
                'depositedAt' => Carbon::instance($faker->dateTime),
                'result' => UserBillingResult::inProgress(),
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),
            $this->generateUserBilling([
                'id' => 20,
                'organizationId' => $this->organizations[0]->id,
                'depositedAt' => Carbon::instance($faker->dateTime),
                'result' => UserBillingResult::inProgress(),
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),
            $this->generateUserBilling([
                'id' => 21,
                'organizationId' => $this->organizations[0]->id,
                'depositedAt' => Carbon::instance($faker->dateTime),
                'result' => UserBillingResult::inProgress(),
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),
            $this->generateUserBilling([
                'id' => 22,
                'organizationId' => $this->organizations[0]->id,
                'depositedAt' => Carbon::instance($faker->dateTime),
                'result' => UserBillingResult::inProgress(),
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'transactedAt' => Carbon::instance($faker->dateTime),
            ], $faker),
            $updatable->copy([
                'id' => 23,
                'dwsItem' => null,
                'user' => $this->generateUserBillingUser($faker),
            ]),
            $updatable->copy([
                'id' => 24,
                'ltcsItem' => null,
                'user' => $this->generateUserBillingUser($faker),
            ]),
            $updatable->copy([
                'id' => 25,
                'otherItems' => [],
                'user' => $this->generateUserBillingUser($faker),
            ]),
            // 請求結果が「請求なし」の利用者請求
            $updatable->copy([
                'id' => 26,
                'result' => UserBillingResult::none(),
            ]),
        ];
    }

    /**
     * Generate an example of UserBilling.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\UserBilling\UserBilling
     */
    protected function generateUserBilling(array $overwrites, Generator $faker): UserBilling
    {
        $attrs = [
            'id' => 1,
            'organizationId' => $this->organizations[0]->id,
            'userId' => $this->users[0]->id,
            'officeId' => $this->offices[0]->id,
            'user' => $this->generateUserBillingUser($faker),
            'office' => UserBillingOffice::create([
                'name' => '事業所テスト',
                'corporationName' => '事業所テスト',
                'addr' => new Addr(
                    postcode: $this->toZingerPostCodeFormat($faker->postcode),
                    prefecture: Prefecture::okinawa(),
                    city: $faker->city,
                    street: $faker->streetAddress,
                    apartment: $faker->streetSuffix,
                ),
                'tel' => '012-245-6789',
            ]),
            'dwsItem' => UserBillingDwsItem::create([
                'dwsStatementId' => $this->dwsBillingStatements[0]->id,
                'score' => 100,
                'unitCost' => Decimal::fromInt(10_0000),
                'subtotalCost' => 1000,
                'tax' => ConsumptionTaxRate::ten(),
                'medicalDeductionAmount' => 5000,
                'benefitAmount' => 2000,
                'subsidyAmount' => 1000,
                'totalAmount' => 1000,
                'copayWithoutTax' => 2000,
                'copayWithTax' => 2200,
            ]),
            'ltcsItem' => UserBillingLtcsItem::create([
                'ltcsStatementId' => $this->ltcsBillingStatements[5]->id,
                'score' => 100,
                'unitCost' => Decimal::fromInt(10_0000),
                'subtotalCost' => 1000,
                'tax' => ConsumptionTaxRate::ten(),
                'medicalDeductionAmount' => 5000,
                'benefitAmount' => 2000,
                'subsidyAmount' => 1000,
                'totalAmount' => 1000,
                'copayWithoutTax' => 2000,
                'copayWithTax' => 2200,
            ]),
            'otherItems' => [
                UserBillingOtherItem::create([
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'totalAmount' => 1000,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                UserBillingOtherItem::create([
                    'score' => 200,
                    'unitCost' => Decimal::fromInt(20_0000),
                    'subtotalCost' => 2000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 10000,
                    'totalAmount' => 2000,
                    'copayWithoutTax' => 4000,
                    'copayWithTax' => 4400,
                ]),
            ],
            'result' => UserBillingResult::paid(),
            'carriedOverAmount' => 1000,
            'withdrawalResultCode' => WithdrawalResultCode::done(),
            'providedIn' => Carbon::create(2021, 4),
            'issuedOn' => Carbon::create(2021, 4, 1),
            'depositedAt' => Carbon::instance($faker->dateTime),
            'transactedAt' => Carbon::instance($faker->dateTime),
            'deductedOn' => Carbon::create(2021, 4, 1),
            'dueDate' => Carbon::create(2021, 4, 1),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return UserBilling::create($overwrites + $attrs);
    }

    /**
     * Generate an example of UserBillingUser.
     *
     * @param \Faker\Generator $faker
     * @return \Domain\UserBilling\UserBillingUser
     */
    protected function generateUserBillingUser(Generator $faker): UserBillingUser
    {
        return UserBillingUser::create([
            'name' => $faker->name($faker->randomElement([Sex::male(), Sex::female()])),
            'addr' => $faker->addr,
            'contacts' => [
                Contact::create([
                    'tel' => '01-2345-6789',
                    'relationship' => ContactRelationship::family(),
                    'name' => '田中花子',
                ]),
                Contact::create([
                    'tel' => '01-1111-2222',
                    'relationship' => ContactRelationship::lawyer(),
                    'name' => '佐藤太郎',
                ]),
            ],
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::agent(),
                'paymentMethod' => PaymentMethod::withdrawal(),
                'contractNumber' => '0123456789',
                'corporationName' => 'ユースタイルラボラトリー株式会社',
                'agentName' => '山田太郎',
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'tel' => '03-1234-5678',
            ]),
            'bankAccount' => UserBillingBankAccount::create([
                'bankName' => $faker->text(100),
                'bankCode' => $faker->numerify(str_repeat('#', 4)),
                'bankBranchName' => $faker->text(100),
                'bankBranchCode' => $faker->numerify(str_repeat('#', 3)),
                'bankAccountType' => $faker->randomElement(BankAccountType::all()),
                'bankAccountNumber' => $faker->numerify(str_repeat('#', 7)),
                'bankAccountHolder' => $faker->text(100),
            ]),
        ]);
    }
}

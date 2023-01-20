<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\Organization\OrganizationSetting;
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
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Illuminate\Support\Arr;
use Lib\Exceptions\LogicException;
use Lib\KanaConverter;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\ZenginDataRecord} のテスト.
 */
final class ZenginDataRecordTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected ZenginDataRecord $zenginDataRecord;
    protected array $values = [];

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'bankCode' => '0005',
                'bankBranchCode' => '798',
                'bankAccountType' => BankAccountType::ordinaryDeposit(),
                'bankAccountNumber' => '1234567',
                'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                'amount' => 198000,
                'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                'clientNumber' => '12345678901234567890',
                'withdrawalResultCode' => WithdrawalResultCode::done(),
            ];
            $self->zenginDataRecord = ZenginDataRecord::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should(
            'return valid ZenginDataRecord when user\'s bank is the JP Bank and valid account number',
            function (): void {
                $bankAccount = $this->createBankAccount(BankAccount::JAPAN_POST_BANK_BANK_CODE, '00054321');
                $userBillings = Seq::from($this->createUserBilling($bankAccount));
                $orgSetting = $this->createOrganizationSetting();
                $zenginDataRecordCode = ZenginDataRecordCode::firstTime();
                $expected = $this->createExpectedZenginDataRecord($userBillings, $orgSetting, $zenginDataRecordCode);
                $this->assertModelStrictEquals(
                    $expected,
                    ZenginDataRecord::from($userBillings, $orgSetting, $zenginDataRecordCode)
                );
            }
        );
        $this->should(
            'return valid ZenginDataRecord when user\'s bank is not the JP Bank and valid account number',
            function (): void {
                $bankAccount = $this->createBankAccount('0005', '0005432');
                $userBillings = Seq::from($this->createUserBilling($bankAccount));
                $orgSetting = $this->createOrganizationSetting();
                $zenginDataRecordCode = ZenginDataRecordCode::firstTime();
                $expected = $this->createExpectedZenginDataRecord($userBillings, $orgSetting, $zenginDataRecordCode);
                $this->assertModelStrictEquals(
                    $expected,
                    ZenginDataRecord::from($userBillings, $orgSetting, $zenginDataRecordCode)
                );
            }
        );
        $this->should('throw LogicException when userBilling is empty', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                ZenginDataRecord::from(
                    Seq::empty(),
                    $this->createOrganizationSetting(),
                    ZenginDataRecordCode::firstTime()
                );
            });
        });
        $this->should(
            'throw LogicException when user\'s bank is the JP Bank and invalid account number (8 digits and ends in other than 1)',
            function (): void {
                $bankAccount = $this->createBankAccount(BankAccount::JAPAN_POST_BANK_BANK_CODE, '00054322');
                $userBillings = Seq::from($this->createUserBilling($bankAccount));
                $this->assertThrows(LogicException::class, function () use ($userBillings): void {
                    ZenginDataRecord::from(
                        $userBillings,
                        $this->createOrganizationSetting(),
                        ZenginDataRecordCode::firstTime()
                    );
                });
            }
        );
        $this->should(
            'throw LogicException when user\'s bank is the JP Bank and invalid account number (over 8 digits)',
            function (): void {
                $bankAccount = $this->createBankAccount(BankAccount::JAPAN_POST_BANK_BANK_CODE, '000543221');
                $userBillings = Seq::from($this->createUserBilling($bankAccount));
                $this->assertThrows(LogicException::class, function () use ($userBillings): void {
                    ZenginDataRecord::from(
                        $userBillings,
                        $this->createOrganizationSetting(),
                        ZenginDataRecordCode::firstTime()
                    );
                });
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'bankCode' => ['bankCode'],
            'bankBranchCode' => ['bankBranchCode'],
            'bankAccountType' => ['bankAccountType'],
            'bankAccountNumber' => ['bankAccountNumber'],
            'bankAccountHolder' => ['bankAccountHolder'],
            'amount' => ['amount'],
            'dataRecordCode' => ['dataRecordCode'],
            'clientNumber' => ['clientNumber'],
            'withdrawalResultCode' => ['withdrawalResultCode'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->zenginDataRecord->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->zenginDataRecord);
        });
    }

    /**
     * 利用者請求：銀行口座を作る.
     *
     * @param string $bankCode
     * @param string $bankAccountNumber
     * @return \Domain\UserBilling\UserBillingBankAccount
     */
    private function createBankAccount(string $bankCode, string $bankAccountNumber): UserBillingBankAccount
    {
        return UserBillingBankAccount::create([
            'bankName' => 'テスト銀行名',
            'bankCode' => $bankCode,
            'bankBranchName' => '銀行支店名',
            'bankBranchCode' => '0123456789',
            'bankAccountType' => BankAccountType::ordinaryDeposit(),
            'bankAccountNumber' => $bankAccountNumber,
            'bankAccountHolder' => 'テスト名義￥',
        ]);
    }

    /**
     * 利用者請求を作る.
     *
     * @param \Domain\UserBilling\UserBillingBankAccount $bankAccount
     * @return \Domain\UserBilling\UserBilling
     */
    private function createUserBilling(UserBillingBankAccount $bankAccount): UserBilling
    {
        return UserBilling::create([
            'id' => 1,
            'organizationId' => 1,
            'userId' => 1,
            'officeId' => 1,
            'user' => UserBillingUser::create([
                'name' => new StructuredName(
                    familyName: 'てすと',
                    givenName: 'たろう',
                    phoneticFamilyName: 'テスト',
                    phoneticGivenName: 'タロウ',
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
                        'relationship' => ContactRelationship::family(),
                        'name' => '田中花子',
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
                'bankAccount' => $bankAccount,
            ]),
            'office' => UserBillingOffice::create(),
            'dwsItem' => UserBillingDwsItem::create(['totalAmount' => 30]),
            'ltcsItem' => UserBillingLtcsItem::create(['totalAmount' => 40]),
            'otherItems' => [
                UserBillingOtherItem::create(['totalAmount' => 20]),
                UserBillingOtherItem::create(['totalAmount' => 20]),
            ],
            'result' => UserBillingResult::paid(),
            'carriedOverAmount' => 1000,
            'withdrawalResultCode' => WithdrawalResultCode::done(),
            'providedIn' => Carbon::create(),
            'issuedOn' => Carbon::create(),
            'depositedAt' => Carbon::create(),
            'transactedAt' => Carbon::create(),
            'deductedOn' => Carbon::create(),
            'dueDate' => Carbon::create(),
            'createdAt' => Carbon::create(),
            'updatedAt' => Carbon::create(),
        ]);
    }

    /**
     * 事業者別設定を作る.
     *
     * @return \Domain\Organization\OrganizationSetting
     */
    private function createOrganizationSetting(): OrganizationSetting
    {
        return OrganizationSetting::create([
            'id' => 1,
            'organizationId' => 1,
            'bankingClientCode' => '0123456789',
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * 検証用の全銀データレコードを作る.
     *
     * @param \Domain\UserBilling\UserBilling[]&\ScalikePHP\Seq $userBillings
     * @param \Domain\Organization\OrganizationSetting $organizationSetting
     * @param \Domain\UserBilling\ZenginDataRecordCode $zenginDataRecordCode
     * @return \Domain\UserBilling\ZenginDataRecord
     */
    private function createExpectedZenginDataRecord(
        Seq $userBillings,
        OrganizationSetting $organizationSetting,
        ZenginDataRecordCode $zenginDataRecordCode,
    ): ZenginDataRecord {
        /** @var \Domain\UserBilling\UserBillingUser $user */
        $user = $userBillings->head()->user;
        $bankAccount = $user->bankAccount;
        $bankAccountNumber = $bankAccount->bankCode === BankAccount::JAPAN_POST_BANK_BANK_CODE
            ? substr($bankAccount->bankAccountNumber, 0, -1)
            : $bankAccount->bankAccountNumber;
        $halfWidthKatakanaBankAccountHolder = KanaConverter::toUppercaseHalfWidthKatakana($bankAccount->bankAccountHolder);
        return ZenginDataRecord::create([
            'bankCode' => $bankAccount->bankCode,
            'bankBranchCode' => $bankAccount->bankBranchCode,
            'bankAccountType' => $bankAccount->bankAccountType,
            'bankAccountNumber' => $bankAccountNumber,
            'bankAccountHolder' => str_replace('￥', '¥', $halfWidthKatakanaBankAccountHolder),
            'amount' => $userBillings->map(fn (UserBilling $x): int => $x->totalAmount)->sum(),
            'dataRecordCode' => $zenginDataRecordCode,
            'clientNumber' => $organizationSetting->bankingClientCode . $user->billingDestination->contractNumber,
            'withdrawalResultCode' => WithdrawalResultCode::done(),
        ]);
    }
}

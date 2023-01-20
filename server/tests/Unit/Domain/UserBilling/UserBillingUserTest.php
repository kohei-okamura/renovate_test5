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
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\User;
use Domain\User\UserBillingDestination;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingUser;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingUser} のテスト
 */
final class UserBillingUserTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBillingUser $userBillingUser;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'name' => new StructuredName(
                    familyName: '土屋',
                    givenName: '花子',
                    phoneticFamilyName: 'ツチヤ',
                    phoneticGivenName: 'ハナコ',
                ),
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'contacts' => [],
                'billingDestination' => UserBillingDestination::create(),
                'bankAccount' => UserBillingBankAccount::create(),
            ];
            $self->userBillingUser = UserBillingUser::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $name = new StructuredName(
            familyName: 'てすと',
            givenName: 'たろう',
            phoneticFamilyName: 'テスト',
            phoneticGivenName: 'タロウ',
        );
        $addr = new Addr(
            postcode: '164-0011',
            prefecture: Prefecture::tokyo(),
            city: '中野区',
            street: '中央1-35-6',
            apartment: 'レッチフィールド中野坂上ビル6F',
        );
        $contact = Contact::create([
            'tel' => '01-2345-6789',
            'relationship' => ContactRelationship::family(),
            'name' => '田中花子',
        ]);
        $billingDestination = UserBillingDestination::create([
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
        ]);
        $user = User::create(
            [
                'id' => 1,
                'organizationId' => 1,
                'name' => $name,
                'addr' => $addr,
                'contacts' => [$contact],
                'billingDestination' => $billingDestination,
            ]
        );
        $bankAccount = BankAccount::create(
            [
                'bankName' => 'テスト銀行名',
                'bankCode' => 'テスト銀行コード',
                'bankBranchName' => '銀行支店名',
                'bankBranchCode' => '0123456789',
                'bankAccountType' => BankAccountType::ordinaryDeposit(),
                'bankAccountNumber' => '0123456789',
                'bankAccountHolder' => 'テスト名義',
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]
        );
        $this->assertModelStrictEquals(
            UserBillingUser::create([
                'name' => $name,
                'addr' => $addr,
                'contacts' => [$contact],
                'billingDestination' => $billingDestination,
                'bankAccount' => UserBillingBankAccount::create([
                    'bankName' => 'テスト銀行名',
                    'bankCode' => 'テスト銀行コード',
                    'bankBranchName' => '銀行支店名',
                    'bankBranchCode' => '0123456789',
                    'bankAccountType' => BankAccountType::ordinaryDeposit(),
                    'bankAccountNumber' => '0123456789',
                    'bankAccountHolder' => 'テスト名義',
                ]),
            ]),
            UserBillingUser::from($user, $bankAccount)
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'name' => ['name'],
            'addr' => ['addr'],
            'contacts' => ['contacts'],
            'billingDestination' => ['billingDestination'],
            'bankAccount' => ['bankAccount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBillingUser->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->userBillingUser);
        });
    }
}

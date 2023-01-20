<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\BankAccount\BankAccountType;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\Pdf\PdfSupport;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\UserBillingDestination;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingPdfSupport;
use Domain\UserBilling\UserBillingReceiptPdf;
use Domain\UserBilling\UserBillingReceiptPdfBillingDestination;
use Domain\UserBilling\UserBillingUser;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingReceiptPdf} のテスト.
 */
final class UserBillingReceiptPdfTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;
    use PdfSupport;
    use UserBillingPdfSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $actual = $this->createInstance();
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            // FYI: テスト実行ごとにランダムに生成される `examples` を用いているためスナップショットテストができない
            $user = $this->examples->users[0];
            $billing = $this->examples->userBillings[0];
            $amounts = self::calculateAmounts($billing);
            $depositedAt = $billing->depositedAt === null ? '' : $billing->depositedAt->toJapaneseDate();
            $issuedOn = Carbon::parse('2021-11-10');
            $expected = UserBillingReceiptPdf::create([
                'billingDestination' => UserBillingReceiptPdfBillingDestination::from($user),
                'carriedOverAmount' => $billing->carriedOverAmount,
                'depositedAt' => $depositedAt,
                'dwsItem' => $billing->dwsItem,
                'issuedOn' => $issuedOn->toJapaneseDate(),
                'ltcsItem' => $billing->ltcsItem,
                'medicalDeductionAmount' => $amounts['medicalDeductionAmount'],
                'normalTaxRate' => $amounts['normalTaxRate'],
                'office' => $billing->office,
                'otherItemsTotalAmount' => $amounts['otherItemsTotalAmount'],
                'period' => CarbonRange::ofMonth($billing->providedIn),
                'providedIn' => $billing->providedIn->toJapaneseYearMonth(),
                'reducedTaxRate' => $amounts['reducedTaxRate'],
                'totalAmount' => $billing->totalAmount,
                'user' => $billing->user,
            ]);

            $actual = UserBillingReceiptPdf::from($user, $billing, $issuedOn);

            $this->assertModelStrictEquals($expected, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createInstance();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\UserBilling\UserBillingReceiptPdf
     */
    private function createInstance(array $attrs = []): UserBillingReceiptPdf
    {
        $user = $this->examples->users[0];
        $billing = $this->examples->userBillings[0];
        $amounts = self::calculateAmounts($billing);
        $depositedAt = $billing->depositedAt === null ? '' : $billing->depositedAt->toJapaneseDate();
        $values = [
            'billingDestination' => UserBillingReceiptPdfBillingDestination::from($user),
            'carriedOverAmount' => $billing->carriedOverAmount,
            'depositedAt' => $depositedAt,
            'dwsItem' => $billing->dwsItem,
            'issuedOn' => Carbon::parse('2021-11-10')->toJapaneseDate(),
            'ltcsItem' => $billing->ltcsItem,
            'medicalDeductionAmount' => $amounts['medicalDeductionAmount'],
            'normalTaxRate' => $amounts['normalTaxRate'],
            'office' => self::createOffice(),
            'otherItemsTotalAmount' => $amounts['otherItemsTotalAmount'],
            'period' => CarbonRange::ofMonth($billing->providedIn),
            'providedIn' => $billing->providedIn->toJapaneseYearMonth(),
            'reducedTaxRate' => $amounts['reducedTaxRate'],
            'totalAmount' => $billing->totalAmount,
            'user' => self::createUser(),
        ];
        return UserBillingReceiptPdf::create($attrs + $values);
    }

    /**
     * テスト用の利用者請求：事業所を返す
     *
     * @return \Domain\UserBilling\UserBillingOffice
     */
    private static function createOffice(): UserBillingOffice
    {
        return UserBillingOffice::create([
            'name' => '事務所名',
            'corporationName' => '法人名',
            'addr' => new Addr(
                postcode: '164-0000',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: 'どこだろう',
                apartment: 'ハーモニータワー',
            ),
            'tel' => '090-0000-0000',
        ]);
    }

    /**
     * テスト用の利用者請求：利用者を返す
     *
     * @return \Domain\UserBilling\UserBillingUser
     */
    private static function createUser(): UserBillingUser
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

        return UserBillingUser::create([
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
        ]);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\StructuredName;
use Domain\UserBilling\UserBillingNoticePdf;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

final class UserBillingNoticePdfTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private Carbon $issuedOn;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->issuedOn = Carbon::parse('2021-11-10');
        });
    }

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
            $statement = $this->examples->dwsBillingStatements[18]->copy([
                'user' => DwsBillingUser::create([
                    'userId' => $this->examples->users[0]->id,
                    'dwsCertificationId' => $this->examples->dwsCertifications[0]->id,
                    'dwsNumber' => 1234567890,
                    'name' => new StructuredName(
                        familyName: '固定姓',
                        givenName: '固定名',
                        phoneticFamilyName: 'コテイセイ',
                        phoneticGivenName: 'コテイメイ',
                    ),
                    'childName' => new StructuredName(
                        familyName: '固定児童姓',
                        givenName: '固定児童名',
                        phoneticFamilyName: 'コテイジドウセイ',
                        phoneticGivenName: 'コテイジドウメイ',
                    ),
                    'copayLimit',
                ]),
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 1,
                        subtotalScore: 10000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 700000,
                        unmanagedCopay: 10000,
                        managedCopay: 200000,
                        cappedCopay: 37200,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 37200,
                        subtotalBenefit: 100000,
                        subtotalSubsidy: 100000,
                    ),
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 1,
                        subtotalScore: 10000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 700000,
                        unmanagedCopay: 10000,
                        managedCopay: 200000,
                        cappedCopay: 37200,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 37200,
                        subtotalBenefit: 100000,
                        subtotalSubsidy: 100000,
                    ),
                ],
            ]);
            $actual = UserBillingNoticePdf::from(
                $this->examples->users[16],
                $statement,
                $this->examples->dwsBillingBundles[0],
                $this->examples->userBillings[14],
                $this->issuedOn
            );
            $this->assertMatchesModelSnapshot($actual);
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
     * @return \Domain\UserBilling\UserBillingNoticePdf
     */
    private function createInstance(array $attrs = []): UserBillingNoticePdf
    {
        $providedIn = $this->issuedOn->subMonth();
        $values = [
            'dwsNumber' => 1234567890,
            'userAddr' => $this->examples->users[16]->addr,
            'dwsBillingUser' => $this->examples->userBillings[14]->user,
            'providedIn' => $providedIn->toJapaneseYearMonth(),
            'issuedOn' => $this->issuedOn->toJapaneseDate(),
            'cityName' => '中野区',
            'dwsServiceDivision' => '重度訪問介護',
            'office' => $this->examples->userBillings[14]->office,
            'subtotalFee' => 150000,
            'subtotalCopay' => 50000,
            'receiptedAmount' => 100000,
        ];
        return UserBillingNoticePdf::create($attrs + $values);
    }
}

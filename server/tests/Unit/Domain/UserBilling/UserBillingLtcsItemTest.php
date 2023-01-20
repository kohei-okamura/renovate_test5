<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Billing\LtcsBillingStatementInsurance;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsBillingUser;
use Domain\Billing\LtcsCarePlanAuthor;
use Domain\Billing\LtcsExpiredReason;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Decimal;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\UserBilling\UserBillingLtcsItem;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingLtcsItem} のテスト
 */
class UserBillingLtcsItemTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBillingLtcsItem $userBillingLtcsItem;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserBillingLtcsItemTest $self): void {
            $self->values = [
                'ltcsStatementId' => 1,
                'score' => 100,
                'unitCost' => 10,
                'subtotalCost' => 1000,
                'tax' => ConsumptionTaxRate::ten(),
                'medicalDeductionAmount' => 5000,
                'benefitAmount' => 2000,
                'subsidyAmount' => 1000,
                'totalAmount' => 1000,
                'copayWithoutTax' => 2000,
                'copayWithTax' => 2200,
            ];
            $self->userBillingLtcsItem = UserBillingLtcsItem::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $user = new LtcsBillingUser(
            userId: 46457795,
            ltcsInsCardId: 18011460,
            insNumber: '1568891108',
            name: new StructuredName(
                familyName: '富樫',
                givenName: '友香',
                phoneticFamilyName: 'トガシ',
                phoneticGivenName: 'トモカ',
            ),
            sex: Sex::female(),
            birthday: Carbon::create(1970, 8, 28),
            ltcsLevel: LtcsLevel::careLevel4(),
            activatedOn: Carbon::create(2017, 4, 16),
            deactivatedOn: Carbon::create(1988, 10, 19),
        );
        $statement = new LtcsBillingStatement(
            id: 1,
            billingId: 93152532,
            bundleId: 78289017,
            insurerNumber: '519388',
            insurerName: '盛岡市',
            user: $user,
            carePlanAuthor: new LtcsCarePlanAuthor(
                authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                officeId: 97811357,
                code: '1321928942',
                name: '株式会社 桐山',
            ),
            agreedOn: Carbon::create(1999, 8, 27),
            expiredOn: Carbon::create(1976, 9, 4),
            expiredReason: LtcsExpiredReason::admittedToWelfareFacility(),
            insurance: new LtcsBillingStatementInsurance(
                benefitRate: 90,
                totalScore: 13147,
                claimAmount: 134887,
                copayAmount: 14988,
            ),
            subsidies: [
                LtcsBillingStatementSubsidy::empty(),
                LtcsBillingStatementSubsidy::empty(),
                LtcsBillingStatementSubsidy::empty(),
            ],
            items: [
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('112145'), // 身体4・Ⅰ
                    serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                    unitScore: 796,
                    count: 8,
                    totalScore: 6368,
                    subsidies: [
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: '',
                ),
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('112169'), // 身4生2・Ⅰ
                    serviceCodeCategory: LtcsServiceCodeCategory::physicalCareAndHousework(),
                    unitScore: 956,
                    count: 4,
                    totalScore: 3824,
                    subsidies: [
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: '',
                ),
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('118001'), // 生活2・Ⅰ
                    serviceCodeCategory: LtcsServiceCodeCategory::housework(),
                    unitScore: 220,
                    count: 8,
                    totalScore: 1760,
                    subsidies: [
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: '',
                ),
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('116274'), // 訪問介護処遇改善加算Ⅱ
                    serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition2(),
                    unitScore: 1195,
                    count: 1,
                    totalScore: 1195,
                    subsidies: [
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: '',
                ),
            ],
            aggregates: [
                new LtcsBillingStatementAggregate(
                    serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                    serviceDays: 20,
                    plannedScore: 11952,
                    managedScore: 11952,
                    unmanagedScore: 1195,
                    insurance: new LtcsBillingStatementAggregateInsurance(
                        totalScore: 13147,
                        unitCost: Decimal::fromInt(11_4000),
                        claimAmount: 134887,
                        copayAmount: 14988,
                    ),
                    subsidies: [
                        LtcsBillingStatementAggregateSubsidy::empty(),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                    ],
                ),
            ],
            appendix: null,
            status: LtcsBillingStatus::checking(),
            fixedAt: Carbon::create(1975, 7, 15, 9, 32, 0),
            createdAt: Carbon::create(1993, 2, 18, 11, 57, 24),
            updatedAt: Carbon::create(2020, 7, 18, 3, 50, 48),
        );
        $this->should('return an instance when the statement has no appendix', function () use ($statement): void {
            // 総単位数 = 6,368単位 + 3,824単位 + 1,760単位 + 1,195単位 = 13,147単位
            // 総単位数（加算を除く） = 6,368単位 + 3,824単位 + 1,760単位 = 11,952単位
            // 総単位数（身体介護を伴う） = 6,368単位 + 3,824単位 = 10,192単位
            // 種類支給限度基準を超える単位数 = 0単位
            // 区分支給限度基準を超える単位数 = 0単位
            // 区分支給限度基準内単位数 = 13,147単位 - 0単位 - 0単位 = 13,147単位
            // 総費用額 = 13,147単位 × 11.40円 = 149,875円
            // 総費用額（保険対象） = 13,147単位 × 11.40円 = 149,875円
            // 保険請求額 = 149,875円 × 90% = 134,887円
            // 利用者負担（保険/事業対象分） = 149,875円 - 134,887円 = 14,988円
            // 利用者請求額 = 149,875円 - 134,887円 = 14,988円
            // 医療費控除対象額 = 14,988円 × (10,192単位 / 11,952単位) = 12,780円
            $expected = UserBillingLtcsItem::create([
                'ltcsStatementId' => 1,
                'score' => 13147,
                'unitCost' => Decimal::fromInt(11_4000),
                'subtotalCost' => 149875,
                'tax' => ConsumptionTaxRate::zero(),
                'medicalDeductionAmount' => 12780,
                'benefitAmount' => 134887,
                'subsidyAmount' => 0,
                'totalAmount' => 14988,
                'copayWithoutTax' => 14988,
                'copayWithTax' => 14988,
            ]);
            $this->assertModelStrictEquals($expected, UserBillingLtcsItem::from($statement));
        });
        $this->should('return an instance when the statement has appendix', function () use ($user, $statement): void {
            // 総単位数 = 6,368単位 + 3,824単位 + 1,760単位 + 1,195単位 = 13,147単位
            // 総単位数（加算を除く） = 6,368単位 + 3,824単位 + 1,760単位 = 11,952単位
            // 総単位数（身体介護を伴う） = 6,368単位 + 3,824単位 = 10,192単位
            // 種類支給限度基準を超える単位数 = 452単位
            // 区分支給限度基準を超える単位数 = 1,200単位
            // 区分支給限度基準内単位数 = 10,300単位 + 1,030単位 = 11,330単位
            // 総費用額 = 11,330単位 × 11.40円 = 149,875円
            // 総費用額（保険対象） = 11,330単位 × 11.40円 = 129,162円
            // 保険請求額 = 129,162円 × 90% = 117,938円
            // 利用者負担（保険/事業対象分） = 129,162円 - 117,938円 = 11,224円
            // 利用者請求額 = 149,875円 - 117,938円 = 31,937円
            // 医療費控除対象額 = 31,937円 × (10,192単位 / 11,952単位) = 27,234円
            $statementWithAppendix = $statement->copy([
                'insurance' => new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 11330,
                    claimAmount: 117938,
                    copayAmount: 11224,
                ),
                'aggregates' => [
                    new LtcsBillingStatementAggregate(
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 20,
                        plannedScore: 10300,
                        managedScore: 10300,
                        unmanagedScore: 1030,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 11330,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 117938,
                            copayAmount: 13105,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                'appendix' => new LtcsProvisionReportSheetAppendix(
                    providedIn: Carbon::create(2021, 2, 1),
                    insNumber: $user->insNumber,
                    userName: $user->name->displayName,
                    unmanagedEntries: Seq::from(
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所',
                            officeCode: '1371405083',
                            serviceName: '訪問介護処遇改善加算Ⅱ',
                            serviceCode: '116274',
                            unitScore: 1195,
                            count: 1,
                            wholeScore: 1195,
                            maxBenefitQuotaExcessScore: 45,
                            maxBenefitExcessScore: 120,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                    ),
                    managedEntries: Seq::from(
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所',
                            officeCode: '1371405083',
                            serviceName: '身体4・Ⅰ',
                            serviceCode: '112145',
                            unitScore: 796,
                            count: 8,
                            wholeScore: 6368,
                            maxBenefitQuotaExcessScore: 452,
                            maxBenefitExcessScore: 1200,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所',
                            officeCode: '1371405083',
                            serviceName: '身4生2・Ⅰ',
                            serviceCode: '112169',
                            unitScore: 956,
                            count: 4,
                            wholeScore: 3824,
                            maxBenefitQuotaExcessScore: 452,
                            maxBenefitExcessScore: 1200,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所',
                            officeCode: '1371405083',
                            serviceName: '生活2・Ⅰ',
                            serviceCode: '118001',
                            unitScore: 220,
                            count: 8,
                            wholeScore: 1760,
                            maxBenefitQuotaExcessScore: 452,
                            maxBenefitExcessScore: 1200,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                    ),
                    maxBenefit: LtcsLevel::careLevel4()->maxBenefit(),
                    insuranceClaimAmount: 117938,
                    subsidyClaimAmount: 0,
                    copayAmount: 31937,
                    unitCost: Decimal::fromInt(11_4000),
                ),
            ]);
            $expected = UserBillingLtcsItem::create([
                'ltcsStatementId' => 1,
                'score' => 13147,
                'unitCost' => Decimal::fromInt(11_4000),
                'subtotalCost' => 149875,
                'tax' => ConsumptionTaxRate::zero(),
                'medicalDeductionAmount' => 27234,
                'benefitAmount' => 117938,
                'subsidyAmount' => 0,
                'totalAmount' => 31937,
                'copayWithoutTax' => 31937,
                'copayWithTax' => 31937,
            ]);
            $this->assertModelStrictEquals($expected, UserBillingLtcsItem::from($statementWithAppendix));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'ltcsStatementId' => ['ltcsStatementId'],
            'score' => ['score'],
            'unitCost' => ['unitCost'],
            'subtotalCost' => ['subtotalCost'],
            'tax' => ['tax'],
            'medicalDeductionAmount' => ['medicalDeductionAmount'],
            'benefitAmount' => ['benefitAmount'],
            'subsidyAmount' => ['subsidyAmount'],
            'totalAmount' => ['totalAmount'],
            'copayWithoutTax' => ['copayWithoutTax'],
            'copayWithTax' => ['copayWithTax'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBillingLtcsItem->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->userBillingLtcsItem);
        });
    }
}

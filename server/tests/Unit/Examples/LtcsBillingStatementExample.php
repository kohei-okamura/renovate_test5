<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

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
use Domain\Common\Decimal;
use Domain\Common\DefrayerCategory;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Faker\Generator as FakerGenerator;
use ScalikePHP\Seq;
use Tests\Unit\Faker\Faker;

/**
 * {@link \Domain\Billing\LtcsBillingStatement} Examples.
 *
 * @mixin \Tests\Unit\Examples\LtcsBillingBundleExample
 * @mixin \Tests\Unit\Examples\LtcsInsCardExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @property-read \Domain\Billing\LtcsBillingStatement[] $ltcsBillingStatements
 */
trait LtcsBillingStatementExample
{
    /**
     * 介護保険サービス：明細書の一覧を生成する.
     *
     * @return array|\Domain\Billing\LtcsBillingStatement[]
     */
    protected function ltcsBillingStatements(): array
    {
        $faker = Faker::make(1077424989);
        return [
            $this->generateLtcsBillingStatement($faker, [
                'id' => 1,
                'status' => LtcsBillingStatus::ready(),
            ]),
            $this->generateLtcsBillingStatement($faker, ['id' => 2]),
            $this->generateLtcsBillingStatement($faker, ['id' => 3]),
            $this->generateLtcsBillingStatement($faker, ['id' => 4]),
            $this->generateLtcsBillingStatement($faker, [
                'id' => 5,
                'billingId' => $this->ltcsBillings[1]->id,
                'bundleId' => $this->ltcsBillingBundles[1]->id,
            ]),
            $this->generateLtcsBillingStatement($faker, [
                'id' => 6,
                'billingId' => $this->ltcsBillings[2]->id,
                'bundleId' => $this->ltcsBillingBundles[5]->id,
                'status' => LtcsBillingStatus::ready(),
            ]),
            $this->generateLtcsBillingStatement($faker, [
                'id' => 7,
                'billingId' => $this->ltcsBillings[2]->id,
                'bundleId' => $this->ltcsBillingBundles[5]->id,
                'status' => LtcsBillingStatus::ready(),
            ]),
            $this->generateLtcsBillingStatement($faker, [
                'id' => 8,
                'billingId' => $this->ltcsBillingBundles[4]->billingId,
                'bundleId' => $this->ltcsBillingBundles[4]->id,
                'user' => LtcsBillingUser::from($this->users[4], $this->ltcsInsCards[16]),
            ]),
            $this->generateLtcsBillingStatement($faker, [
                'id' => 9,
                'billingId' => $this->ltcsBillings[6]->id,
                'bundleId' => $this->ltcsBillingBundles[0]->id,
                'user' => LtcsBillingUser::from($this->users[4], $this->ltcsInsCards[16]),
            ]),
            $this->generateLtcsBillingStatement($faker, [
                'id' => 10,
                'billingId' => $this->ltcsBillingBundles[6]->billingId,
                'bundleId' => $this->ltcsBillingBundles[6]->id,
                'insurance' => new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 11952,
                    claimAmount: 122626,
                    copayAmount: 13626,
                ),
                'subsidies' => [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                'items' => [
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
                ],
                'aggregates' => [
                    new LtcsBillingStatementAggregate(
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 20,
                        plannedScore: 11952,
                        managedScore: 11952,
                        unmanagedScore: 0,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 11952,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 122626,
                            copayAmount: 13626,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                'appendix' => new LtcsProvisionReportSheetAppendix(
                    providedIn: Carbon::instance($faker->dateTime)->startOfMonth(),
                    insNumber: $this->ltcsInsCards[2]->insNumber,
                    userName: $this->users[3]->name->displayName,
                    unmanagedEntries: Seq::empty(),
                    managedEntries: Seq::from(
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所 ',
                            officeCode: '1234567890',
                            serviceName: '身体4・Ⅰ',
                            serviceCode: '112145',
                            unitScore: 796,
                            count: 8,
                            wholeScore: 6368,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所 ',
                            officeCode: '1234567890',
                            serviceName: '身4生2・Ⅰ',
                            serviceCode: '112169',
                            unitScore: 956,
                            count: 4,
                            wholeScore: 3824,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所 ',
                            officeCode: '1234567890',
                            serviceName: '生活2・Ⅰ',
                            serviceCode: '118001',
                            unitScore: 220,
                            count: 8,
                            wholeScore: 1760,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                    ),
                    maxBenefit: $this->ltcsInsCards[2]->ltcsLevel->maxBenefit(),
                    insuranceClaimAmount: 122626,
                    subsidyClaimAmount: 0,
                    copayAmount: 13626,
                    unitCost: Decimal::fromInt(11_4000),
                ),
            ]),
            // 請求額が 0 円
            $this->generateLtcsBillingStatement($faker, [
                'id' => 11,
                'billingId' => $this->ltcsBillingBundles[8]->billingId,
                'bundleId' => $this->ltcsBillingBundles[8]->id,
                'insurance' => new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 11952,
                    claimAmount: 122626,
                    copayAmount: 0,
                ),
                'subsidies' => [
                    new LtcsBillingStatementSubsidy(
                        defrayerCategory: DefrayerCategory::livelihoodProtection(),
                        defrayerNumber: '123456',
                        recipientNumber: '1234567',
                        benefitRate: 100,
                        totalScore: 11952,
                        claimAmount: 13626,
                        copayAmount: 0,
                    ),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                'items' => [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('112145'), // 身体4・Ⅰ
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 796,
                        count: 8,
                        totalScore: 6368,
                        subsidies: [
                            new LtcsBillingStatementItemSubsidy(8, 6368),
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
                            new LtcsBillingStatementItemSubsidy(4, 3824),
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
                            new LtcsBillingStatementItemSubsidy(8, 1760),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                ],
                'aggregates' => [
                    new LtcsBillingStatementAggregate(
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 20,
                        plannedScore: 11952,
                        managedScore: 11952,
                        unmanagedScore: 0,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 11952,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 122626,
                            copayAmount: 13626,
                        ),
                        subsidies: [
                            new LtcsBillingStatementAggregateSubsidy(
                                totalScore: 11952,
                                claimAmount: 13626,
                                copayAmount: 0,
                            ),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                'appendix' => new LtcsProvisionReportSheetAppendix(
                    providedIn: Carbon::instance($faker->dateTime)->startOfMonth(),
                    insNumber: $this->ltcsInsCards[2]->insNumber,
                    userName: $this->users[3]->name->displayName,
                    unmanagedEntries: Seq::empty(),
                    managedEntries: Seq::from(
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所 ',
                            officeCode: '1234567890',
                            serviceName: '身体4・Ⅰ',
                            serviceCode: '112145',
                            unitScore: 796,
                            count: 8,
                            wholeScore: 6368,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所 ',
                            officeCode: '1234567890',
                            serviceName: '身4生2・Ⅰ',
                            serviceCode: '112169',
                            unitScore: 956,
                            count: 4,
                            wholeScore: 3824,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: '土屋訪問介護事業所 ',
                            officeCode: '1234567890',
                            serviceName: '生活2・Ⅰ',
                            serviceCode: '118001',
                            unitScore: 220,
                            count: 8,
                            wholeScore: 1760,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 90,
                        ),
                    ),
                    maxBenefit: $this->ltcsInsCards[2]->ltcsLevel->maxBenefit(),
                    insuranceClaimAmount: 122626,
                    subsidyClaimAmount: 13626,
                    copayAmount: 0,
                    unitCost: Decimal::fromInt(11_4000),
                ),
            ]),
        ];
    }

    /**
     * 介護保険サービス：明細書を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingStatement
     */
    private function generateLtcsBillingStatement(FakerGenerator $faker, array $attrs): LtcsBillingStatement
    {
        $user = $this->users[3];
        $insCard = $this->ltcsInsCards[2];
        $x = new LtcsBillingStatement(
            id: null,
            billingId: $this->ltcsBillingBundles[0]->billingId,
            bundleId: $this->ltcsBillingBundles[0]->id,
            insurerNumber: (string)$faker->randomNumber(6, true),
            insurerName: $faker->addr->city,
            user: LtcsBillingUser::from($user, $insCard),
            carePlanAuthor: new LtcsCarePlanAuthor(
                authorType: $faker->randomElement(LtcsCarePlanAuthorType::all()),
                officeId: $this->offices[0]->id,
                code: $this->offices[0]->ltcsCareManagementService->code,
                name: $this->offices[0]->name,
            ),
            agreedOn: Carbon::parse($faker->date()),
            expiredOn: Carbon::parse($faker->date()),
            expiredReason: $faker->randomElement(LtcsExpiredReason::all()),
            insurance: new LtcsBillingStatementInsurance(
                benefitRate: 90,
                totalScore: $faker->numberBetween(0, 100000),
                claimAmount: $faker->numberBetween(0, 100000),
                copayAmount: $faker->numberBetween(0, 100000),
            ),
            subsidies: [
                new LtcsBillingStatementSubsidy(
                    defrayerCategory: $faker->randomElement(DefrayerCategory::all()),
                    defrayerNumber: (string)$faker->randomNumber(8, true),
                    recipientNumber: (string)$faker->randomNumber(7, true),
                    benefitRate: 100,
                    totalScore: $faker->numberBetween(0, 100000),
                    claimAmount: $faker->numberBetween(0, 100000),
                    copayAmount: $faker->numberBetween(0, 100000),
                ),
                LtcsBillingStatementSubsidy::empty(),
                LtcsBillingStatementSubsidy::empty(),
            ],
            items: [
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('11' . $faker->randomNumber(4, true)),
                    serviceCodeCategory: LtcsServiceCodeCategory::housework(),
                    unitScore: $faker->numberBetween(0, 2000),
                    count: $faker->numberBetween(1, 30),
                    totalScore: $faker->numberBetween(2000, 600000),
                    subsidies: [
                        new LtcsBillingStatementItemSubsidy(
                            count: $faker->numberBetween(1, 30),
                            totalScore: $faker->numberBetween(2000, 600000),
                        ),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: $faker->text(30),
                ),
            ],
            aggregates: [
                new LtcsBillingStatementAggregate(
                    serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                    serviceDays: $faker->numberBetween(1, 30),
                    plannedScore: $faker->numberBetween(2000, 600000),
                    managedScore: $faker->numberBetween(2000, 600000),
                    unmanagedScore: $faker->numberBetween(2000, 600000),
                    insurance: new LtcsBillingStatementAggregateInsurance(
                        totalScore: $faker->numberBetween(2000, 600000),
                        unitCost: Decimal::fromInt($faker->numberBetween(10_0000, 11_4000)),
                        claimAmount: $faker->numberBetween(2000, 600000),
                        copayAmount: $faker->numberBetween(2000, 600000),
                    ),
                    subsidies: [
                        new LtcsBillingStatementAggregateSubsidy(
                            totalScore: $faker->numberBetween(2000, 600000),
                            claimAmount: $faker->numberBetween(2000, 600000),
                            copayAmount: $faker->numberBetween(2000, 600000),
                        ),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                    ],
                ),
            ],
            appendix: new LtcsProvisionReportSheetAppendix(
                providedIn: Carbon::instance($faker->dateTime)->startOfMonth(),
                insNumber: $insCard->insNumber,
                userName: $user->name->displayName,
                unmanagedEntries: Seq::from(
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '処遇改善加算Ⅰ',
                        serviceCode: '116275',
                        unitScore: 1035,
                        count: 1,
                        wholeScore: 1035,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                ),
                managedEntries: Seq::from(
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '身体3・Ⅰ',
                        serviceCode: '112097',
                        unitScore: 695,
                        count: 4,
                        wholeScore: 2780,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '身3生2・Ⅰ',
                        serviceCode: '112121',
                        unitScore: 856,
                        count: 4,
                        wholeScore: 3424,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '生活3・夜・Ⅰ',
                        serviceCode: '118014',
                        unitScore: 337,
                        count: 4,
                        wholeScore: 1348,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                ),
                maxBenefit: $faker->numberBetween(9600, 36400),
                insuranceClaimAmount: $faker->numberBetween(100000, 999999),
                subsidyClaimAmount: $faker->numberBetween(100000, 999999),
                copayAmount: $faker->numberBetween(100000, 999999),
                unitCost: Decimal::fromInt(11_4000),
            ),
            status: $faker->randomElement(LtcsBillingStatus::all()),
            fixedAt: Carbon::instance($faker->dateTime),
            createdAt: Carbon::instance($faker->dateTime),
            updatedAt: Carbon::instance($faker->dateTime),
        );
        return $x->copy($attrs);
    }
}

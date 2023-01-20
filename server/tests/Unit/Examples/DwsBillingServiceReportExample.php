<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\StructuredName;
use Faker\Generator;

/**
 * DwsBillingServiceReport Examples.
 *
 * @property-read \Domain\Billing\DwsBillingServiceReport[] $dwsBillingServiceReports
 * @mixin \Tests\Unit\Examples\DwsBillingBundleExample
 */
trait DwsBillingServiceReportExample
{
    /**
     * サービス提供実績記録票の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingServiceReport[]
     */
    protected function dwsBillingServiceReports(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        return [
            $this->generateDwsBillingServiceReport([
                'id' => 1,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
                //                'format' => DwsBillingServiceReportFormat::homeHelpService(),
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 2,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 3,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 4,
                'dwsBillingId' => $this->dwsBillingBundles[4]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[4]->id,
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 5,
                'dwsBillingId' => $this->dwsBillingBundles[4]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[4]->id,
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 6,
                'dwsBillingId' => $this->dwsBillingBundles[5]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[5]->id,
                'format' => DwsBillingServiceReportFormat::homeHelpService(),
                'plan' => DwsBillingServiceReportAggregate::fromAssoc([
                    DwsBillingServiceReportAggregateGroup::housework()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(1_0000),
                    ],
                    DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(10_0000),
                    ],
                ]),
                'result' => DwsBillingServiceReportAggregate::fromAssoc([
                    DwsBillingServiceReportAggregateGroup::housework()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(2_0000),
                    ],
                    DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(5_0000),
                    ],
                ]),
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 7,
                'dwsBillingId' => $this->dwsBillingBundles[5]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[5]->id,
                'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
                'plan' => DwsBillingServiceReportAggregate::fromAssoc([
                    DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(1_0000),
                    ],
                ]),
                'result' => DwsBillingServiceReportAggregate::fromAssoc([
                    DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(3_0000),
                    ],
                    DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(9_0000),
                    ],
                ]),
            ]),
            // ID: 8, 9 はサービス提供実績記録状態一括更新の E2E で使用している
            // サービス提供実績記録票の状態が「未確定」、かつ紐づく請求の状態が「確定済」ではない
            $this->generateDwsBillingServiceReport([
                'id' => 8,
                'dwsBillingId' => $this->dwsBillingBundles[9]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[9]->id,
                'status' => DwsBillingStatus::ready(),
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 9,
                'dwsBillingId' => $this->dwsBillingBundles[9]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[9]->id,
                'status' => DwsBillingStatus::ready(),
            ]),
            $this->generateDwsBillingServiceReport([
                'id' => 10,
                'dwsBillingId' => $this->dwsBillingBundles[10]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[10]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[2]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
            ]),
        ];
    }

    /**
     * Generate an example of DwsBillingServiceReport.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingServiceReport
     */
    private function generateDwsBillingServiceReport(array $overwrites): DwsBillingServiceReport
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $values = [
            'user' => DwsBillingUser::create([
                'userId' => $this->users[0]->id,
                'dwsCertificationId' => $this->dwsCertifications[9]->id,
                'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                'name' => StructuredName::empty(),
                'childName' => StructuredName::empty(),
                'copayLimit' => $faker->numberBetween(),
            ]),
            'format' => $faker->randomElement(DwsBillingServiceReportFormat::all()),
            'plan' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_0000),
                ],
            ]),
            'result' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_0000),
                ],
            ]),
            'emergencyCount' => $faker->numberBetween(0, 2000),
            'firstTimeCount' => $faker->numberBetween(0, 2000),
            'welfareSpecialistCooperationCount' => $faker->numberBetween(0, 2000),
            'behavioralDisorderSupportCooperationCount' => $faker->numberBetween(0, 2000),
            'movingCareSupportCount' => $faker->numberBetween(0, 2000),
            'items' => [
                DwsBillingServiceReportItem::create([
                    'serialNumber' => $faker->numberBetween(0, 10),
                    'providedOn' => Carbon::today(),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd1(),
                    'providerType' => $faker->randomElement(DwsBillingServiceReportProviderType::all()),
                    'situation' => DwsBillingServiceReportSituation::hospitalized(),
                    'plan' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::today(),
                            'end' => Carbon::today()->addMonths(6),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt($faker->numberBetween(2, 10) * 5000),
                        'movingDurationHours' => Decimal::fromInt($faker->numberBetween(2, 10) * 5000),
                    ]),
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::today(),
                            'end' => Carbon::today()->addMonths(6),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt($faker->numberBetween(2, 10) * 5000),
                        'movingDurationHours' => Decimal::fromInt($faker->numberBetween(2, 10) * 5000),
                    ]),
                    'serviceCount' => $faker->numberBetween(0, 10),
                    'headcount' => $faker->numberBetween(0, 3),
                    'isCoaching' => $faker->boolean,
                    'isFirstTime' => $faker->boolean,
                    'isEmergency' => $faker->boolean,
                    'isWelfareSpecialistCooperation' => $faker->boolean,
                    'isBehavioralDisorderSupportCooperation' => $faker->boolean,
                    'isMovingCareSupport' => $faker->boolean,
                    'isDriving' => $faker->boolean,
                    'isPreviousMonth' => $faker->boolean,
                    'note' => 'sample',
                ]),
            ],
            'status' => $faker->randomElement(DwsBillingStatus::all()),
            'fixedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsBillingServiceReport::create($overwrites + $values);
    }
}

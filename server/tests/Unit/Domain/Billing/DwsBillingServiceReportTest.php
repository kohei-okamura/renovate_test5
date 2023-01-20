<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReport} のテスト.
 */
final class DwsBillingServiceReportTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private DwsBillingServiceReport $report;

    private array $aggregate = [];
    private array $items = [];
    private array $user = [];
    private array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->user = [
                'userId' => $self->examples->users[0]->id,
                'dwsCertificationId' => $self->examples->dwsCertifications[9]->id,
                'dwsNumber' => '0123456789',
                'name' => 'ナマエ',
                'childName' => 'オナマエ',
                'copayLimit' => 10000,
            ];
            $self->aggregate = [
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(15_5000),
                ],
            ];
            $self->items = [
                'serialNumber' => 1,
                'providedOn' => Carbon::create(2020, 11),
                'serviceCount' => 2,
                'serviceType' => DwsGrantedServiceCode::housework(),
                'providerType' => DwsBillingServiceReportProviderType::beginner(),
                'isDriving' => true,
                'period' => CarbonRange::create([
                    'start' => Carbon::parse('2020-10-01'),
                    'end' => Carbon::parse('2020-11-01'),
                ]),
                'serviceDurationHours' => Decimal::fromInt(10_5000),
                'movingDurationHours' => Decimal::fromInt(1_5000),
                'headcount' => 1,
                'isPreviousMonth' => true,
                'note' => '',
                'situation' => DwsBillingServiceReportSituation::hospitalized(),
                'isEmergency' => true,
                'isFirstTime' => true,
                'isWelfareSpecialistCooperation' => true,
                'isBehavioralDisorderSupportCooperation' => true,
                'isCoaching' => true,
                'indexNumber' => 1,
            ];
            $self->values = [
                'dwsBillingId' => 3,
                'dwsBillingBundleId' => 1,
                'user' => DwsBillingUser::create($self->user),
                'format' => DwsBillingServiceReportFormat::homeHelpService(),
                'plan' => [
                    DwsBillingServiceReportAggregate::fromAssoc($self->aggregate),
                ],
                'result' => [
                    DwsBillingServiceReportAggregate::fromAssoc($self->aggregate),
                ],
                'firstTimeCount' => 150,
                'welfareSpecialistCooperationCount' => 150,
                'behavioralDisorderSupportCooperationCount' => 150,
                'movingCareSupportCount' => 150,
                'items' => [
                    DwsBillingServiceReport::create($self->items),
                ],
                'status' => DwsBillingStatus::checking(),
            ];
            $self->report = DwsBillingServiceReport::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $this->assertMatchesModelSnapshot($this->report);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $this->assertMatchesJsonSnapshot($this->report);
        });
    }
}

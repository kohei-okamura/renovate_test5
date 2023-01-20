<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Billing\DwsHomeHelpServiceUnit;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportAggregate} のテスト.
 */
final class DwsBillingServiceReportAggregateTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;
    use ExamplesConsumer;

    protected DwsBillingServiceReportAggregate $aggregate;

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        $this->should('return a Decimal', function (): void {
            $group = DwsBillingServiceReportAggregateGroup::physicalCare();
            $category = DwsBillingServiceReportAggregateCategory::category100();
            $value = Decimal::fromInt(123_45, 2);
            $aggregate = DwsBillingServiceReportAggregate::fromAssoc([
                $group->value() => [$category->value() => $value],
            ]);
            $expected = $value;

            $actual = $aggregate->get($group, $category);

            $this->assertInstanceOf(Decimal::class, $actual);
            $this->assertSame($expected->toInt(4), $actual->toInt(4));
        });
        $this->should('return zero when it does not exist', function (): void {
            $group = DwsBillingServiceReportAggregateGroup::physicalCare();
            $category = DwsBillingServiceReportAggregateCategory::category100();
            $value = Decimal::fromInt(123_45, 2);
            $aggregate = DwsBillingServiceReportAggregate::fromAssoc([
                $group->value() => [$category->value() => $value],
            ]);

            $actual = $aggregate->get(DwsBillingServiceReportAggregateGroup::housework(), $category);

            $this->assertTrue($actual->isZero());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromAssoc(): void
    {
        $this->should('throw Exception when GroupID is invalid', function (): void {
            $assoc = [
                // GroupIDの有効な値は11〜15
                100 => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(123_45, 2),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::fromInt(123_45, 2),
                ],
            ];
            $this->assertThrows(
                InvalidArgumentException::class,
                function () use ($assoc): void {
                    DwsBillingServiceReportAggregate::fromAssoc($assoc);
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $group = DwsBillingServiceReportAggregateGroup::physicalCare()->value();
            $item = [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(123_45, 2),
            ];
            $aggregate = DwsBillingServiceReportAggregate::fromAssoc([$group => $item]);

            // 何故か自前で JSON に変換しないと動かない……
            // NOTE: コピペに用いないこと！
            $this->assertMatchesJsonSnapshot(json_encode($aggregate));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_forHomeHelpService(): void
    {
        // インターフェース仕様書のNo.ごとにパターンを作る.
        // テストパターンが全く足りないが大変なので、とりあえず基本のパターンと問題があった人数複数パターンを追加する.
        // 通常
        $this->should('Case 1', function (): void {
            $unit = DwsHomeHelpServiceUnit::create([
                'fragment' => DwsHomeHelpServiceFragment::create([
                    'providerType' => DwsHomeHelpServiceProviderType::none(),
                    'isSecondary' => false,
                    'range' => CarbonRange::create([
                        'start' => Carbon::create(2021, 10, 1, 10, 0),
                        'end' => Carbon::create(2021, 10, 1, 11, 30),
                    ]),
                    'headcount' => 1,
                ]),
                'isEmergency' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'isPlannedByNovice' => false,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'category' => DwsServiceCodeCategory::physicalCare(),
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 10, 1, 10, 0),
                    'end' => Carbon::create(2021, 10, 1, 11, 30),
                ]),
                'isTerminated' => true,
                'serviceDuration' => 90,
            ]);
            $actual = DwsBillingServiceReportAggregate::forHomeHelpService(Seq::from($unit));
            $this->assertMatchesJsonSnapshot(json_encode($actual));
        });
        // ヘルパー要件あり
        $this->should('Case 2', function (): void {
            $unit = DwsHomeHelpServiceUnit::create([
                'fragment' => DwsHomeHelpServiceFragment::create([
                    'providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                    'isSecondary' => false,
                    'range' => CarbonRange::create([
                        'start' => Carbon::create(2021, 10, 1, 10, 0),
                        'end' => Carbon::create(2021, 10, 1, 12, 0),
                    ]),
                    'headcount' => 1,
                ]),
                'isEmergency' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'isPlannedByNovice' => false,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'category' => DwsServiceCodeCategory::physicalCare(),
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 10, 1, 10, 0),
                    'end' => Carbon::create(2021, 10, 1, 12, 0),
                ]),
                'isTerminated' => true,
                'serviceDuration' => 120,
            ]);
            $actual = DwsBillingServiceReportAggregate::forHomeHelpService(Seq::from($unit));
            $this->assertMatchesJsonSnapshot(json_encode($actual));
        });
        // 同一時間二人派遣
        $this->should('Case 4', function (): void {
            $unit = DwsHomeHelpServiceUnit::create([
                'fragment' => DwsHomeHelpServiceFragment::create([
                    'providerType' => DwsHomeHelpServiceProviderType::none(),
                    'isSecondary' => false,
                    'range' => CarbonRange::create([
                        'start' => Carbon::create(2021, 10, 1, 10, 0),
                        'end' => Carbon::create(2021, 10, 1, 11, 0),
                    ]),
                    'headcount' => 2,
                ]),
                'isEmergency' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'isPlannedByNovice' => false,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'category' => DwsServiceCodeCategory::physicalCare(),
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 10, 1, 10, 0),
                    'end' => Carbon::create(2021, 10, 1, 11, 0),
                ]),
                'isTerminated' => true,
                'serviceDuration' => 60,
            ]);
            $actual = DwsBillingServiceReportAggregate::forHomeHelpService(Seq::from($unit));
            $this->assertMatchesJsonSnapshot(json_encode($actual));
        });
    }
}

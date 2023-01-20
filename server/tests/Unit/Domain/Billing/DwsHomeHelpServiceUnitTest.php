<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Billing\DwsHomeHelpServiceUnit;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

final class DwsHomeHelpServiceUnitTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;
    use ExamplesConsumer;
    use CarbonMixin;

    protected DwsHomeHelpServiceUnit $homeHelpServiceUnit;

    protected Carbon $providedIn;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceUnitTest $self): void {
            $self->values = [
                'fragment' => DwsHomeHelpServiceFragment::create([
                    'providerType' => DwsHomeHelpServiceProviderType::none(),
                    'isSecondary' => true,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addHour()]),
                    'headcount' => 2,
                ]),
                'isEmergency' => true,
                'isFirst' => true,
                'isWelfareSpecialistCooperation' => true,
                'isPlannedByNovice' => true,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'category' => DwsServiceCodeCategory::housework(),
                'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addHour()]),
                'serviceDuration' => 60,
            ];
            $self->providedIn = Carbon::parse('2021-12-01')->firstOfMonth();
            $self->homeHelpServiceUnit = DwsHomeHelpServiceUnit::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'fragment' => ['fragment'],
            'isEmergency' => ['isEmergency'],
            'isFirst' => ['isFirst'],
            'isWelfareSpecialistCooperation' => ['isWelfareSpecialistCooperation'],
            'isPlannedByNovice' => ['isPlannedByNovice'],
            'buildingType' => ['buildingType'],
            'category' => ['category'],
            'range' => ['range'],
            'serviceDuration' => ['serviceDuration'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->homeHelpServiceUnit->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromHomeHelpServiceChunk(): void
    {
        // TODO: パターン数が少ないので必要に応じて追加する。そもそもテストがよろしくないので直す
        $this->should('Unit generated from chunk when category is physicalCare', function (): void {
            $chunk = $this->generateExampleSimpleChunk(40, DwsServiceCodeCategory::physicalCare());
            $expected = $this->generateExampleUnit($chunk->fragments[0], $chunk, true, 60);
            $actual = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, Carbon::now()->firstOfMonth());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('Unit generated from chunk when category is housework', function (): void {
            $chunk = $this->generateExampleSimpleChunk(40, DwsServiceCodeCategory::housework());
            $expected = $this->generateExampleUnit($chunk->fragments[0], $chunk, true, 45);
            $actual = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, Carbon::now()->firstOfMonth());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('Unit generated from chunk when category is accompanyWithPhysicalCare', function (): void {
            $chunk = $this->generateExampleSimpleChunk(80, DwsServiceCodeCategory::accompanyWithPhysicalCare());
            $expected = $this->generateExampleUnit($chunk->fragments[0], $chunk, true, 90);
            $actual = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, Carbon::now()->firstOfMonth());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('Unit generated from chunk when category is accompany', function (): void {
            $chunk = $this->generateExampleSimpleChunk(105, DwsServiceCodeCategory::accompany());
            $expected = $this->generateExampleUnit($chunk->fragments[0], $chunk, true, 120);
            $actual = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, Carbon::now()->firstOfMonth());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('Unit generated from chunk when chunk has multiple fragment', function (): void {
            $range1 = $this->generateExampleRange(160, Carbon::create(2021, 12, 2, 9)); // 9:00 〜 11:40
            $range2 = $this->generateExampleRange(130, Carbon::create(2021, 12, 2, 10, 30)); // 10:30 〜 12:40
            $longRange = $this->generateExampleRange(220, Carbon::create(2021, 12, 2, 9)); // 9:00 〜 12:40
            $fragment1 = $this->generateExampleFragment([
                'range' => $range1,
            ]);
            $fragment2 = $this->generateExampleFragment([
                'range' => $range2,
            ]);
            $chunk = $this->generateExampleChunk([
                'category' => DwsServiceCodeCategory::physicalCare(),
                'range' => $longRange,
                'fragments' => Seq::from($fragment1, $fragment2),
            ]);
            $units = Seq::from(
                $this->generateExampleUnit($fragment1, $chunk, false),
                $this->generateExampleUnit($fragment2, $chunk, true, 300),// (160 + 130)を 30 分単位に丸める
            );
            $actualUnits = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, $this->providedIn);
            $this->assertEach(
                function (DwsHomeHelpServiceUnit $expected, DwsHomeHelpServiceUnit $actual): void {
                    $this->assertModelStrictEquals($expected, $actual);
                },
                $units->toArray(),
                $actualUnits->toArray()
            );
        });
        $this->should('前月から跨いだサービスが存在する場合に前月分と当月分の ServiceUnit を生成する', function (): void {
            $range = $this->generateExampleRange(120, Carbon::create(2021, 11, 30, 23)); // 23:00 〜 01:00
            $fragment = $this->generateExampleFragment([
                'range' => $range,
            ]);
            $chunk = $this->generateExampleChunk([
                'category' => DwsServiceCodeCategory::physicalCare(),
                'range' => $range,
                'fragments' => Seq::from($fragment),
            ]);
            $range1 = CarbonRange::create([
                'start' => Carbon::create(2021, 11, 30, 23),
                'end' => Carbon::create(2021, 12, 1, 0),
            ]);
            $range2 = CarbonRange::create([
                'start' => Carbon::create(2021, 12, 1, 0),
                'end' => Carbon::create(2021, 12, 1, 1),
            ]);
            $units = Seq::from(
                $this->generateExampleUnit($fragment->copy(['range' => $range1]), $chunk, false),
                $this->generateExampleUnit($fragment->copy(['range' => $range2]), $chunk, true, 60)// 12月分のサービス提供時間の60
            );
            $actualUnits = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, $this->providedIn);
            $this->assertEach(
                function (DwsHomeHelpServiceUnit $expected, DwsHomeHelpServiceUnit $actual): void {
                    $this->assertModelStrictEquals($expected, $actual);
                },
                $units->toArray(),
                $actualUnits->toArray()
            );
        });
        $this->should('翌月に跨いだサービスが存在する場合に当月分のみ ServiceUnit を生成する', function (): void {
            $range = $this->generateExampleRange(120, Carbon::create(2021, 12, 31, 23)); // 23:00 〜 01:00
            $fragment = $this->generateExampleFragment([
                'range' => $range,
            ]);
            $chunk = $this->generateExampleChunk([
                'category' => DwsServiceCodeCategory::physicalCare(),
                'range' => $range,
                'fragments' => Seq::from($fragment),
            ]);
            $range = CarbonRange::create([
                'start' => Carbon::create(2021, 12, 31, 23),
                'end' => Carbon::create(2022, 1, 1, 0),
            ]);
            $units = Seq::from(
                $this->generateExampleUnit($fragment->copy(['range' => $range]), $chunk, true, 60),
            );
            $actualUnits = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, $this->providedIn);
            $this->assertEach(
                function (DwsHomeHelpServiceUnit $expected, DwsHomeHelpServiceUnit $actual): void {
                    $this->assertModelStrictEquals($expected, $actual);
                },
                $units->toArray(),
                $actualUnits->toArray()
            );
        });
        $this->specify('重研の身体介護で30分のサービスの場合に時間数が60で計算されること', function (): void {
            $fragment = $this->generateExampleFragment([
                'providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                'isSecondary' => false,
                'range' => $this->generateExampleRange(30),
                'headcount' => 1,
            ]);
            $chunk = $this->generateExampleChunk([
                'userId' => $this->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::physicalCare(),
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isEmergency' => false,
                'isPlannedByNovice' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'range' => $this->generateExampleRange(30),
                'fragments' => Seq::from($fragment),
            ]);
            $actual = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, Carbon::now()->firstOfMonth());
            // serviceDuration が60となる
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->specify('身体介護で30分のサービスの場合に時間数が30で計算されること', function (): void {
            $fragment = $this->generateExampleFragment([
                'providerType' => DwsHomeHelpServiceProviderType::none(),
                'isSecondary' => false,
                'range' => $this->generateExampleRange(30),
                'headcount' => 1,
            ]);
            $chunk = $this->generateExampleChunk([
                'userId' => $this->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::physicalCare(),
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isEmergency' => false,
                'isPlannedByNovice' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'range' => $this->generateExampleRange(30),
                'fragments' => Seq::from($fragment),
            ]);
            $actual = DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($chunk, Carbon::now()->firstOfMonth());
            // serviceDuration が30となる
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
            $this->assertMatchesJsonSnapshot(DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($this->generateExampleChunk(), Carbon::now()->firstOfMonth())->toArray());
        });
    }

    /**
     * テスト用の時間範囲を生成する.
     *
     * @param int $durationMinutes 未指定の場合は 30
     * @param null|\Domain\Common\Carbon $start 未指定の場合は Carbon::now()
     * @return \Domain\Common\CarbonRange
     */
    protected function generateExampleRange(int $durationMinutes = 30, Carbon $start = null): CarbonRange
    {
        $s = $start ?? Carbon::now();
        return CarbonRange::create([
            'start' => $s,
            'end' => $s->addMinutes($durationMinutes),
        ]);
    }

    /**
     * テスト用の障害福祉サービス請求：サービス単位（居宅介護）要素を生成する.
     *
     * @param array $override 上書き用
     * @return \Domain\Billing\DwsHomeHelpServiceFragment
     */
    protected function generateExampleFragment(array $override = []): DwsHomeHelpServiceFragment
    {
        return DwsHomeHelpServiceFragment::create([
            'providerType' => DwsHomeHelpServiceProviderType::none(),
            'isSecondary' => false,
            'range' => $this->generateExampleRange(),
            'headcount' => 1,
        ])->copy($override);
    }

    /**
     * テスト用のサービス単位を生成する.
     *
     * @param array $override 上書き用
     * @return \Domain\Billing\DwsHomeHelpServiceChunk
     */
    protected function generateExampleChunk(array $override = []): DwsHomeHelpServiceChunk
    {
        return DwsHomeHelpServiceChunkImpl::create([
            'userId' => $this->examples->users[0]->id,
            'category' => DwsServiceCodeCategory::housework(),
            'buildingType' => DwsHomeHelpServiceBuildingType::none(),
            'isEmergency' => true,
            'isPlannedByNovice' => true,
            'isFirst' => true,
            'isWelfareSpecialistCooperation' => true,
            'range' => $this->generateExampleRange(),
            'fragments' => Seq::from($this->generateExampleFragment()),
        ])->copy($override);
    }

    /**
     * テスト用の単純なサービス単位を生成する.
     *
     * @param int $durationMinutes
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\Billing\DwsHomeHelpServiceChunk
     */
    protected function generateExampleSimpleChunk(
        int $durationMinutes,
        DwsServiceCodeCategory $category
    ): DwsHomeHelpServiceChunk {
        $range = $this->generateExampleRange($durationMinutes);
        $fragment = $this->generateExampleFragment([
            'range' => $range,
        ]);
        return $this->generateExampleChunk([
            'category' => $category,
            'range' => $range,
            'fragments' => Seq::from($fragment),
        ]);
    }

    /**
     * テスト（検証）用の障害福祉サービス請求：サービス実績単位（居宅介護）を生成する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceFragment $fragment
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param bool $isTerminated
     * @param null|int $serviceDuration
     * @return \Domain\Billing\DwsHomeHelpServiceUnit
     */
    protected function generateExampleUnit(
        DwsHomeHelpServiceFragment $fragment,
        DwsHomeHelpServiceChunk $chunk,
        bool $isTerminated,
        ?int $serviceDuration = null
    ): DwsHomeHelpServiceUnit {
        return DwsHomeHelpServiceUnit::create([
            'fragment' => $fragment,
            'isEmergency' => $isTerminated ? $chunk->isEmergency : false,
            'isFirst' => $isTerminated ? $chunk->isFirst : false,
            'isWelfareSpecialistCooperation' => $isTerminated ? $chunk->isWelfareSpecialistCooperation : false,
            'isPlannedByNovice' => $isTerminated ? $chunk->isPlannedByNovice : false,
            'buildingType' => $chunk->buildingType,
            'category' => $chunk->category,
            'range' => $fragment->range,
            'isTerminated' => $isTerminated,
            'serviceDuration' => $isTerminated ? $serviceDuration : null,
        ]);
    }
}

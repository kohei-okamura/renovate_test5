<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsCalcCycle;
use Domain\ServiceCodeDictionary\LtcsCalcScore;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Domain\ServiceCodeDictionary\LtcsCompositionType;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry} のテスト.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry
     */
    private function createInstance(array $attrs = []): LtcsHomeVisitLongTermCareDictionaryEntry
    {
        $values = [
            'id' => 604,
            'dictionaryId' => 517,
            'serviceCode' => ServiceCode::fromString('112417'),
            'name' => '身9生1・2人・深・Ⅰ',
            'category' => LtcsServiceCodeCategory::physicalCareAndHousework(),
            'headcount' => 2,
            'compositionType' => LtcsCompositionType::composed(),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
            'noteRequirement' => LtcsNoteRequirement::durationMinutes(),
            'isLimited' => true,
            'isBulkSubtractionTarget' => true,
            'isSymbioticSubtractionTarget' => true,
            'score' => LtcsCalcScore::create([
                'value' => 1141,
                'calcType' => LtcsCalcType::baseScore(),
                'calcCycle' => LtcsCalcCycle::perService(),
            ]),
            'extraScore' => LtcsCalcScore::create([
                'isAvailable' => true,
                'baseMinutes' => 240,
                'unitScore' => 83,
                'unitMinutes' => 30,
                'specifiedOfficeAdditionCoefficient' => 120,
                'timeframeAdditionCoefficient' => 150,
            ]),
            'timeframe' => Timeframe::midnight(),
            'totalMinutes' => IntRange::create([
                'start' => 260,
                'end' => 9999,
            ]),
            'physicalMinutes' => IntRange::create([
                'start' => 240,
                'end' => 9999,
            ]),
            'houseworkMinutes' => IntRange::create([
                'start' => 20,
                'end' => 45,
            ]),
            'createdAt' => Carbon::now()->subDay(),
            'updatedA' => Carbon::now(),
        ];
        return LtcsHomeVisitLongTermCareDictionaryEntry::create($attrs + $values);
    }
}

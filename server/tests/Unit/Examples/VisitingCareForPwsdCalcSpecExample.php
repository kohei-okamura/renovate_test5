<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Faker\Generator;

/**
 * VisitingCareForPwsdCalcSpec Example.
 *
 * @property-read \Domain\Office\VisitingCareForPwsdCalcSpec[] $visitingCareForPwsdCalcSpecs
 * @mixin \Tests\Unit\Examples\OfficeExample
 */
trait VisitingCareForPwsdCalcSpecExample
{
    /**
     * 事業所算定情報（障害・重度訪問介護）の一覧を生成する.
     *
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec[]
     */
    protected function visitingCareForPwsdCalcSpecs(): array
    {
        return [
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 1,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 1),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
            ]),
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 2,
                'officeId' => $this->offices[9]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 3, 1),
                    'end' => Carbon::create(2021, 3, 31),
                ]),
            ]),
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 3,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 5, 1),
                    'end' => Carbon::create(2021, 5, 31),
                ]),
            ]),
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 4,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 1),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'officeId' => $this->offices[2]->id,
            ]),
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 5,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 9, 1),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'officeId' => $this->offices[0]->id,
                'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::none(),
                'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::none(),
            ]),
            // 事業所詳細取得時の算定情報のソート確認に使用（id: 6 〜 9 まで）
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 6,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 10),
                    'end' => Carbon::create(2021, 12, 25),
                ]),
                'createdAt' => Carbon::create(2021, 1, 5),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 登録日時が早い
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 7,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 10),
                    'end' => Carbon::create(2021, 12, 25),
                ]),
                'createdAt' => Carbon::create(2021, 1, 3),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 適用期間開始日が遅い
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 8,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 12),
                    'end' => Carbon::create(2021, 12, 25),
                ]),
                'createdAt' => Carbon::create(2021, 1, 5),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 適用期間終了日が遅い
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 9,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 10),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'createdAt' => Carbon::create(2021, 1, 5),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 福祉・介護職員等ベースアップ等支援加算
            $this->generateVisitingCareForPwsdCalcSpec([
                'id' => 10,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2022, 10, 01),
                    'end' => Carbon::create(2025, 12, 31),
                ]),
                'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::none(),
                'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::none(),
                'baseIncreaseSupportAddition' => DwsBaseIncreaseSupportAddition::addition1(),
                'officeId' => $this->offices[2]->id,
            ]),
        ];
    }

    /**
     * Generate an example of VisitingCareForPwsdCalcSpec.
     *
     * @param array $overwrites
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec
     */
    protected function generateVisitingCareForPwsdCalcSpec(array $overwrites): VisitingCareForPwsdCalcSpec
    {
        $faker = app(Generator::class);
        $values = [
            'officeId' => $this->offices[0]->id,
            'period' => CarbonRange::create([
                'start' => Carbon::today(),
                'end' => Carbon::today()->addMonths(6),
            ]),
            'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::addition1(),
            'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::addition2(),
            'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => DwsBaseIncreaseSupportAddition::none(),
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return VisitingCareForPwsdCalcSpec::create($overwrites + $values);
    }
}

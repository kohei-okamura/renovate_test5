<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Faker\Generator;

/**
 * HomeVisitLongTermCareCalcSpec Example.
 *
 * @property-read \Domain\Office\HomeVisitLongTermCareCalcSpec[] $homeVisitLongTermCareCalcSpecs
 * @mixin \Tests\Unit\Examples\OfficeExample
 */
trait HomeVisitLongTermCareCalcSpecExample
{
    /**
     * 事業所算定情報（介保・訪問介護）の一覧を生成する.
     *
     * @return \Domain\Office\HomeVisitLongTermCareCalcSpec[]
     */
    protected function homeVisitLongTermCareCalcSpecs(): array
    {
        return [
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 1,
            ]),
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 2,
                'officeId' => $this->offices[9]->id,
            ]),
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 3,
            ]),
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 4,
                'officeId' => $this->offices[2]->id,
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            ]),
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 5,
                'officeId' => $this->offices[0]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::parse('2019-01'),
                    'end' => Carbon::parse('2021-12'),
                ]),
            ]),
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 6,
                'officeId' => $this->offices[2]->id,
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
            ]),
            // 事業所詳細取得時の算定情報のソート確認に使用（id: 7 〜 10 まで）
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 7,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 10),
                    'end' => Carbon::create(2021, 12, 25),
                ]),
                'createdAt' => Carbon::create(2021, 1, 5),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 登録日時が早い
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 8,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 10),
                    'end' => Carbon::create(2021, 12, 25),
                ]),
                'createdAt' => Carbon::create(2021, 1, 3),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 適用期間開始日が遅い
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 9,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 12),
                    'end' => Carbon::create(2021, 12, 25),
                ]),
                'createdAt' => Carbon::create(2021, 1, 5),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
            // 適用期間終了日が遅い
            $this->generateHomeVisitLongTermCareCalcSpec([
                'id' => 10,
                'officeId' => $this->offices[25]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 10),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'createdAt' => Carbon::create(2021, 1, 5),
                'updatedAt' => Carbon::create(2021, 1, 10),
            ]),
        ];
    }

    /**
     * Generate an example of HomeVisitLongTermCareCalcSpec.
     *
     * @param array $overwrites
     * @return \Domain\Office\HomeVisitLongTermCareCalcSpec
     */
    protected function generateHomeVisitLongTermCareCalcSpec(array $overwrites)
    {
        $faker = app(Generator::class);
        $values = [
            'officeId' => $this->offices[0]->id,
            'period' => CarbonRange::create([
                'start' => Carbon::today(),
                'end' => Carbon::today()->addMonths(6),
            ]),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition2(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::mountainousArea(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return HomeVisitLongTermCareCalcSpec::create($overwrites + $values);
    }
}

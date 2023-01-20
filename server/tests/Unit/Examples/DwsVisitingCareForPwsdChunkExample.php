<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;
use ScalikePHP\Seq;

/**
 * DwsVisitingCareForPwsdChunk Example.
 *
 * @property-read \Domain\Billing\DwsVisitingCareForPwsdChunk[] $dwsVisitingCareForPwsdChunks
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsVisitingCareForPwsdChunkExample
{
    /**
     * 障害福祉サービス請求：サービス単位（重度訪問介護）の一覧を生成する.
     *
     * @return array
     */
    protected function dwsVisitingCareForPwsdChunks(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsVisitingCareForPwsdChunk($faker, [
                'id' => 1,
                'userId' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'providedOn' => Carbon::create(2021, 2, 9),
            ]),
            $this->generateDwsVisitingCareForPwsdChunk($faker, [
                'id' => 2,
                'userId' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'providedOn' => Carbon::create(2021, 2, 10),
            ]),
            $this->generateDwsVisitingCareForPwsdChunk($faker, [
                'id' => 3,
                'userId' => $this->users[1]->id,
                'providedOn' => Carbon::create(2021, 2, 11),
            ]),
        ];
    }

    /**
     * ドメインモデルを返す.
     *
     * @param \Faker\Generator $faker ダミーデータ生成機
     * @param array $overwrites デフォルト値を上書きするパラメータ
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk ドメインモデル
     */
    private function generateDwsVisitingCareForPwsdChunk(
        Generator $faker,
        array $overwrites
    ): DwsVisitingCareForPwsdChunk {
        $attrs = [
            'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
            'providedOn' => Carbon::instance($faker->dateTime),
            'isEmergency' => false,
            'isFirst' => false,
            'isBehavioralDisorderSupportCooperation' => false,
            'range' => CarbonRange::create([
                'start' => Carbon::now(),
                'end' => Carbon::now()->addMinute(),
            ]),
            'fragments' => Seq::from(
                DwsVisitingCareForPwsdFragment::create([
                    'isCoaching' => $faker->boolean,
                    'isMoving' => $faker->boolean,
                    'isSecondary' => $faker->boolean,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create([
                        'start' => Carbon::now(),
                        'end' => Carbon::now()->addMinute(),
                    ]),
                    'headcount' => $faker->randomElement([1, 2]),
                ]),
            ),
        ];
        return DwsVisitingCareForPwsdChunkImpl::create($overwrites + $attrs);
    }
}

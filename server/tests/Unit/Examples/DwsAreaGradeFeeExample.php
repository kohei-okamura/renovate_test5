<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\DwsAreaGrade\DwsAreaGradeFee;
use Faker\Generator;

/**
 * DwsAreaGradeFee Example.
 *
 * @property-read \Domain\DwsAreaGrade\DwsAreaGradeFee[] $dwsAreaGradeFees
 */
trait DwsAreaGradeFeeExample
{
    /**
     * 障害福祉サービス：地域区分単価 の一覧を生成する.
     *
     * @return array|\Domain\DwsAreaGrade\DwsAreaGradeFee[]
     */
    protected function dwsAreaGradeFees(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsAreaGradeFee($faker, [
                'id' => 1,
                'dwsAreaGradeId' => $this->dwsAreaGrades[5]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 1),
                'fee' => Decimal::fromInt(11_2000),
            ]),
            $this->generateDwsAreaGradeFee($faker, [
                'id' => 2,
                'dwsAreaGradeId' => $this->dwsAreaGrades[6]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 2),
            ]),
            $this->generateDwsAreaGradeFee($faker, [
                'id' => 3,
                'dwsAreaGradeId' => $this->dwsAreaGrades[7]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 3),
            ]),
            $this->generateDwsAreaGradeFee($faker, [
                'id' => 4,
                'dwsAreaGradeId' => $this->dwsAreaGrades[8]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 4),
            ]),
            $this->generateDwsAreaGradeFee($faker, [
                'id' => 5,
                'dwsAreaGradeId' => $this->dwsAreaGrades[5]->id,
                'effectivatedOn' => Carbon::create(2020, 2, 3),
            ]),
        ];
    }

    /**
     * 障害福祉サービス：地域区分単価 を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\DwsAreaGrade\DwsAreaGradeFee
     */
    private function generateDwsAreaGradeFee(Generator $faker, array $overwrites): DwsAreaGradeFee
    {
        $attrs = [
            'effectivatedOn' => Carbon::parse($faker->date()),
            'fee' => Decimal::fromInt(10_0000),
        ];
        return DwsAreaGradeFee::create($overwrites + $attrs);
    }
}

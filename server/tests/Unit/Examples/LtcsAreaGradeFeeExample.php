<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Faker\Generator;

/**
 * LtcsAreaGradeFee Example.
 *
 * @property-read \Domain\LtcsAreaGrade\LtcsAreaGradeFee[] $ltcsAreaGradeFees
 */
trait LtcsAreaGradeFeeExample
{
    /**
     * 介護保険サービス：地域区分単価 の一覧を生成する.
     *
     * @return array|\Domain\LtcsAreaGrade\LtcsAreaGradeFee[]
     */
    protected function ltcsAreaGradeFees(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateLtcsAreaGradeFee($faker, [
                'id' => 1,
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[4]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 1),
                'fee' => Decimal::fromInt(11_4000),
            ]),
            $this->generateLtcsAreaGradeFee($faker, [
                'id' => 2,
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[5]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 2),
            ]),
            $this->generateLtcsAreaGradeFee($faker, [
                'id' => 3,
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[6]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 3),
            ]),
            $this->generateLtcsAreaGradeFee($faker, [
                'id' => 4,
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[6]->id,
                'effectivatedOn' => Carbon::create(2021, 2, 4),
            ]),
            $this->generateLtcsAreaGradeFee($faker, [
                'id' => 5,
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[4]->id,
                'effectivatedOn' => Carbon::create(2020, 2, 1),
                'fee' => Decimal::fromInt(11_4000),
            ]),
        ];
    }

    /**
     * 地域区分単価を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\LtcsAreaGrade\LtcsAreaGradeFee
     */
    private function generateLtcsAreaGradeFee(Generator $faker, array $overwrites): LtcsAreaGradeFee
    {
        $attrs = [
            'effectivatedOn' => Carbon::parse($faker->date()),
            'fee' => Decimal::fromInt(10_0000),
        ];
        return LtcsAreaGradeFee::create($overwrites + $attrs);
    }
}

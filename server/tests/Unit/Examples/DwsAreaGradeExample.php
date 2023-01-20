<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\DwsAreaGrade\DwsAreaGrade;
use Faker\Generator;

/**
 * DwsAreaGrade Example.
 *
 * @property-read DwsAreaGrade[] $dwsAreaGrades
 */
trait DwsAreaGradeExample
{
    /**
     * 障害福祉サービス地域区分の一覧を生成する.
     *
     * @return \Domain\DwsAreaGrade\DwsAreaGrade[]
     */
    protected function DwsAreaGrades(): array
    {
        return [
            $this->generateDwsAreaGrade([
                'id' => 1,
                'name' => '地域区分テスト',
            ]),
            $this->generateDwsAreaGrade([
                'id' => 2,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 3,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 4,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 5,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 6,
                'name' => '4級地',
            ]),
            $this->generateDwsAreaGrade([
                'id' => 7,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 8,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 9,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 10,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 11,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 12,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 13,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 14,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 15,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 16,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 17,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 18,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 19,
            ]),
            $this->generateDwsAreaGrade([
                'id' => 20,
            ]),
        ];
    }

    /**
     * 障害福祉サービス地域区分を生成する.
     *
     * @param array $overwrites
     * @return \Domain\DwsAreaGrade\DwsAreaGrade
     */
    private function generateDwsAreaGrade(array $overwrites): DwsAreaGrade
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $attrs = [
            'code' => (string)$faker->unique()->numberBetween(10, 99),
            'name' => $faker->unique()->text(10),
        ];
        return DwsAreaGrade::create($overwrites + $attrs);
    }
}

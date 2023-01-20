<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\LtcsAreaGrade\LtcsAreaGrade;
use Faker\Generator;

/**
 * LtcsAreaGrade Example.
 *
 * @property-read \Domain\LtcsAreaGrade\LtcsAreaGrade[] $ltcsAreaGrades
 */
trait LtcsAreaGradeExample
{
    /**
     * 介保地域区分の一覧を生成する.
     *
     * @return \Domain\LtcsAreaGrade\LtcsAreaGrade[]
     */
    protected function LtcsAreaGrades(): array
    {
        return [
            $this->generateLtcsAreaGrade([
                'id' => 1,
                'name' => '介保地域区分テスト',
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 2,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 3,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 4,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 5,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 6,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 7,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 8,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 9,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 10,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 11,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 12,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 13,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 14,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 15,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 16,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 17,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 18,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 19,
            ]),
            $this->generateLtcsAreaGrade([
                'id' => 20,
            ]),
        ];
    }

    /**
     * 介保地域区分を生成する.
     *
     * @param array $overwrites
     * @return \Domain\LtcsAreaGrade\LtcsAreaGrade
     */
    private function generateLtcsAreaGrade(array $overwrites): LtcsAreaGrade
    {
        $faker = app(Generator::class);
        $attrs = [
            'code' => $faker->unique()->numerify(str_repeat('#', 2)),
            'name' => $faker->text(10),
        ];
        return LtcsAreaGrade::create($overwrites + $attrs);
    }
}

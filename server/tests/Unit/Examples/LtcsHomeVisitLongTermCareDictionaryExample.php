<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;
use Faker\Generator as FakerGenerator;
use Tests\Unit\Faker\Faker;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary} Examples.
 *
 * @property-read \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary[] $ltcsHomeVisitLongTermCareDictionaries
 */
trait LtcsHomeVisitLongTermCareDictionaryExample
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $attrs
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary
     */
    public function generateLtcsHomeVisitLongTermCareDictionary(
        FakerGenerator $faker,
        array $attrs
    ): LtcsHomeVisitLongTermCareDictionary {
        $values = [
            'effectivatedOn' => Carbon::parse($faker->date('2021-01-25')),
            'name' => $faker->text(100),
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime('2021-01-25')),
            'updatedAt' => Carbon::instance($faker->dateTime('2021-01-25')),
        ];
        return LtcsHomeVisitLongTermCareDictionary::create($attrs + $values);
    }

    /**
     * 介護保険サービス：訪問介護：サービスコード辞書の一覧を生成する.
     *
     * @return array|\Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary[]
     */
    protected function ltcsHomeVisitLongTermCareDictionaries(): array
    {
        $faker = Faker::make(1899178394);
        return [
            $this->generateLtcsHomeVisitLongTermCareDictionary($faker, [
                'id' => 1,
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionary($faker, [
                'id' => 2,
                'effectivatedOn' => Carbon::parse('2020-01-02'),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionary($faker, [
                'id' => 3,
                'effectivatedOn' => Carbon::parse('2020-01-04'),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionary($faker, [
                'id' => 4,
                'effectivatedOn' => Carbon::parse('2021-04-01'),
            ]),
        ];
    }
}

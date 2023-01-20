<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Faker\Generator;

/**
 * DwsVisitingCareForPwsdDictionary Example.
 *
 * @property-read \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary[] $dwsVisitingCareForPwsdDictionaries
 * @mixin \Tests\Unit\Examples\DwsVisitingCareForPwsdDictionaryExample
 */
trait DwsVisitingCareForPwsdDictionaryExample
{
    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書の一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary[]
     */
    protected function dwsVisitingCareForPwsdDictionaries(): array
    {
        return [
            $this->generateDwsVisitingCareForPwsdDictionary([
                'id' => 20200101,
                'effectivatedOn' => Carbon::instance(Carbon::parse('2020-01-01')),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionary([
                'id' => 20200102,
                'effectivatedOn' => Carbon::parse('2020-01-02'),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionary([
                'id' => 20200103,
                'effectivatedOn' => Carbon::parse('2020-01-04'),
            ]),
        ];
    }

    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書を生成する.
     *
     * @param array $overwrites
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary
     */
    protected function generateDwsVisitingCareForPwsdDictionary(array $overwrites): DwsVisitingCareForPwsdDictionary
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'name' => $faker->text(100),
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsVisitingCareForPwsdDictionary::create($overwrites + $attrs);
    }
}

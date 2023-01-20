<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Faker\Generator;

/**
 * DwsHomeHelpServiceDictionary Example.
 *
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary[] $dwsHomeHelpServiceDictionaries
 * @mixin \Tests\Unit\Examples\DwsHomeHelpServiceDictionaryExample
 */
trait DwsHomeHelpServiceDictionaryExample
{
    /**
     * 障害福祉サービス：居宅介護：サービスコード辞書の一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary[]
     */
    protected function dwsHomeHelpServiceDictionaries(): array
    {
        return [
            $this->generateDwsHomeHelpServiceDictionary([
                'id' => 1,
                'effectivatedOn' => Carbon::instance(Carbon::parse('2020-01-01')),
            ]),
            $this->generateDwsHomeHelpServiceDictionary([
                'id' => 2,
                'effectivatedOn' => Carbon::parse('2020-01-02'),
            ]),
            $this->generateDwsHomeHelpServiceDictionary([
                'id' => 3,
                'effectivatedOn' => Carbon::parse('2020-01-04'),
            ]),
        ];
    }

    /**
     * 障害福祉サービス：居宅介護：サービスコード辞書を生成する.
     *
     * @param array $overwrites
     * @return array|\Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary
     */
    protected function generateDwsHomeHelpServiceDictionary(array $overwrites): DwsHomeHelpServiceDictionary
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'name' => $faker->text(100),
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsHomeHelpServiceDictionary::create($overwrites + $attrs);
    }
}

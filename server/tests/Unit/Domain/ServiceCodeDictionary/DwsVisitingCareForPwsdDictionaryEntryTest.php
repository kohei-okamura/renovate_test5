<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Common\IntRange;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\Timeframe;
use Faker\Generator;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * DwsVisitingCareForPwsdDictionaryEntry のテスト
 */
class DwsVisitingCareForPwsdDictionaryEntryTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsVisitingCareForPwsdDictionaryEntry $dwsVisitingCareForPwsdDictionaryEntry;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdDictionaryEntryTest $self): void {
            /** @var \Faker\Generator $faker */
            $faker = app(Generator::class);
            $self->values = [
                'serviceCode' => ServiceCode::fromString('123123'),
                'name' => $faker->text(100),
                'category' => $faker->randomElement(DwsServiceCodeCategory::all()),
                'isSecondary' => $faker->boolean,
                'isCoaching' => $faker->boolean,
                'isHospitalized' => $faker->boolean,
                'isLongHospitalized' => $faker->boolean,
                'score' => $faker->numberBetween(1, 10),
                'timeframe' => $faker->randomElement(Timeframe::all()),
                'duration' => IntRange::create([
                    'start' => $faker->numberBetween(1, 3),
                    'end' => $faker->numberBetween(4, 6),
                ]),
                'unit' => $faker->randomElement([0, 30, 60]),
            ];
            $self->dwsVisitingCareForPwsdDictionaryEntry = DwsVisitingCareForPwsdDictionaryEntry::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (string $key): void {
            $this->assertSame($this->dwsVisitingCareForPwsdDictionaryEntry->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'serviceCode' => ['serviceCode'],
                'name' => ['name'],
                'category' => ['category'],
                'isSecondary' => ['isSecondary'],
                'isCoaching' => ['isCoaching'],
                'isHospitalized' => ['isHospitalized'],
                'score' => ['score'],
                'timeframe' => ['timeframe'],
                'duration' => ['duration'],
                'unit' => ['unit'],
            ],
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsVisitingCareForPwsdDictionaryEntry);
        });
    }
}

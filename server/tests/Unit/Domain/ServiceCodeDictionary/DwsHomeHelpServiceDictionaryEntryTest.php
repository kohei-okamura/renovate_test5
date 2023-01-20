<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Common\IntRange;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * DwsHomeHelpServiceDictionaryEntry のテスト
 */
class DwsHomeHelpServiceDictionaryEntryTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsHomeHelpServiceDictionaryEntry $dwsHomeHelpServiceDictionaryEntry;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceDictionaryEntryTest $self): void {
            $faker = app(Generator::class);
            $self->values = [
                'id' => 1,
                'dwsHomeHelpServiceDictionaryId' => $faker->numberBetween(1, 3),
                'serviceCode' => ServiceCode::fromString('123123'),
                'name' => $faker->text(100),
                'category' => $faker->randomElement(DwsServiceCodeCategory::all()),
                'isExtra' => $faker->boolean,
                'isSecondary' => $faker->boolean,
                'providerType' => $faker->randomElement(DwsHomeHelpServiceProviderType::all()),
                'isPlannedByNovice' => $faker->boolean,
                'buildingType' => $faker->randomElement(DwsHomeHelpServiceBuildingType::all()),
                'score' => $faker->numberBetween(1, 10),
                'daytimeDuration' => IntRange::create([
                    'start' => $faker->numberBetween(1, 3),
                    'end' => $faker->numberBetween(4, 6),
                ]),
                'morningDuration' => IntRange::create([
                    'start' => $faker->numberBetween(1, 3),
                    'end' => $faker->numberBetween(4, 6),
                ]),
                'nightDuration' => IntRange::create([
                    'start' => $faker->numberBetween(1, 3),
                    'end' => $faker->numberBetween(4, 6),
                ]),
                'midnightDuration1' => IntRange::create([
                    'start' => $faker->numberBetween(1, 3),
                    'end' => $faker->numberBetween(4, 6),
                ]),
                'midnightDuration2' => IntRange::create([
                    'start' => $faker->numberBetween(1, 3),
                    'end' => $faker->numberBetween(4, 6),
                ]),
            ];
            $self->dwsHomeHelpServiceDictionaryEntry = DwsHomeHelpServiceDictionaryEntry::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (string $key): void {
            $this->assertSame($this->dwsHomeHelpServiceDictionaryEntry->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'id' => ['id'],
                'dwsHomeHelpServiceDictionaryId' => ['dwsHomeHelpServiceDictionaryId'],
                'serviceCode' => ['serviceCode'],
                'name' => ['name'],
                'category' => ['category'],
                'isExtra' => ['isExtra'],
                'isSecondary' => ['isSecondary'],
                'providerType' => ['providerType'],
                'isPlannedByNovice' => ['isPlannedByNovice'],
                'score' => ['score'],
                'daytimeDuration' => ['daytimeDuration'],
                'morningDuration' => ['morningDuration'],
                'nightDuration' => ['nightDuration'],
                'midnightDuration1' => ['midnightDuration1'],
                'midnightDuration2' => ['midnightDuration2'],
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
            $this->assertMatchesJsonSnapshot($this->dwsHomeHelpServiceDictionaryEntry);
        });
    }
}

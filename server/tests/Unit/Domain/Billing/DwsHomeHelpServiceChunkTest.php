<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsHomeHelpServiceChunk} Test.
 */
class DwsHomeHelpServiceChunkTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;

    protected DwsHomeHelpServiceChunk $homeHelpServiceChunk;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceChunkTest $self): void {
            $self->values = [
                'id' => 1,
                'userId' => $self->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::physicalCare(),
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isEmergency' => false,
                'isPlannedByNovice' => false,
                'range' => CarbonRange::create([
                    'start' => Carbon::create('2020-11-11 11:00'),
                    'end' => Carbon::create('2020-11-11 12:00'),
                ]),
                'fragments' => Seq::fromArray([
                    DwsHomeHelpServiceFragment::create([
                        'providerType' => DwsHomeHelpServiceProviderType::none(),
                        'isSecondary' => false,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create('2020-11-11 11:00'),
                            'end' => Carbon::create('2020-11-11 12:00'),
                        ]),
                        'headcount' => 2,
                    ]),
                ]),
            ];
            $self->homeHelpServiceChunk = DwsHomeHelpServiceChunkImpl::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'id' => ['id'],
            'userId' => ['userId'],
            'category' => ['category'],
            'buildingType' => ['buildingType'],
            'isEmergency' => ['isEmergency'],
            'isPlannedByNovice' => ['isPlannedByNovice'],
            'range' => ['range'],
            'fragments' => ['fragments'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->homeHelpServiceChunk->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }
}

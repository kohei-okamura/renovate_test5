<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkImpl} Test.
 */
final class DwsVisitingCareForPwsdChunkImplTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;

    protected DwsVisitingCareForPwsdChunkImpl $dwsVisitingCareForPwsdChunk;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (DwsVisitingCareForPwsdChunkImplTest $self): void {
            $self->values = [
                'userId' => 1,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'isEmergency' => false,
                'isFirst' => false,
                'isBehavioralDisorderSupportCooperation' => false,
                'providedOn' => Carbon::now(),
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addHour(),
                ]),
                'fragments' => Seq::from(DwsVisitingCareForPwsdFragment::create()),
            ];
            $self->dwsVisitingCareForPwsdChunk = DwsVisitingCareForPwsdChunkImpl::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'userId' => ['userId'],
            'category' => ['category'],
            'isEmergency' => ['isEmergency'],
            'isFirst' => ['isFirst'],
            'isBehavioralDisorderSupportCooperation' => ['isBehavioralDisorderSupportCooperation'],
            'providedOn' => ['providedOn'],
            'range' => ['range'],
            'fragments' => ['fragments'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsVisitingCareForPwsdChunk->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_isEffective(): void
    {
        $this->should(
            'range is less than 30 minutes',
            function (): void {
                $fragment = Seq::from(DwsVisitingCareForPwsdFragment::create(
                    [
                        'isHospitalized' => false,
                        'isLongHospitalized' => false,
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::now(),
                            'end' => Carbon::now()->addMinutes(29),
                        ]),
                        'headcount' => 1,
                    ]
                ));
                $chunk = DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => 1,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isEmergency' => false,
                    'isFirst' => false,
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create([
                        'start' => Carbon::now(),
                        'end' => Carbon::now()->addMinutes(29),
                    ]),
                    'fragments' => $fragment,
                ]);
                $this->assertFalse($chunk->isEffective());
            },
        );
        $this->should(
            'range is more than 30 minutes',
            function (): void {
                $fragment = Seq::from(DwsVisitingCareForPwsdFragment::create(
                    [
                        'isHospitalized' => false,
                        'isLongHospitalized' => false,
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::now(),
                            'end' => Carbon::now()->addMinutes(30),
                        ]),
                        'headcount' => 1,
                    ]
                ));
                $chunk = DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => 1,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isEmergency' => false,
                    'isFirst' => false,
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create([
                        'start' => Carbon::now(),
                        'end' => Carbon::now()->addMinutes(30),
                    ]),
                    'fragments' => $fragment,
                ]);
                $this->assertTrue($chunk->isEffective());
            },
        );
    }
}

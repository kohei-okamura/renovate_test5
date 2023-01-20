<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Billing\DwsVisitingCareForPwsdUnit;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

final class DwsVisitingCareForPwsdUnitTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;
    use ExamplesConsumer;
    use CarbonMixin;

    protected DwsVisitingCareForPwsdUnit $dwsVisitingCareForPwsdUnit;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdUnitTest $self): void {
            $self->values = [
                'fragment' => DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => true,
                    'isLongHospitalized' => true,
                    'isCoaching' => true,
                    'isMoving' => false,
                    'isSecondary' => true,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addHour()]),
                    'headcount' => 1,
                ]),
            ];
            $self->dwsVisitingCareForPwsdUnit = DwsVisitingCareForPwsdUnit::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'fragment' => ['fragment'],
            'providedOn' => ['providedOn'],
            'isEmergency' => ['isEmergency'],
            'isFirst' => ['isFirst'],
            'category' => ['category'],
            'range' => ['range'],
            'serviceCount' => ['serviceCount'],
            'serviceDuration' => ['serviceDuration'],
            'movingDuration' => ['movingDuration'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsVisitingCareForPwsdUnit->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromHomeHelpServiceChunk(): void
    {
        $this->specify('1 Chunk から DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $actualUnits = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()]),
                    'fragments' => Seq::from($this->values['fragment']),
                ])
            );

            $this->assertMatchesModelSnapshot($actualUnits);
        });
        $this->specify('サービスが30分未満のみの場合に LogicException を投げる', function (): void {
            $fragment = DwsVisitingCareForPwsdFragment::create([
                'isHospitalized' => false,
                'isLongHospitalized' => false,
                'isCoaching' => false,
                'isMoving' => false,
                'isSecondary' => false,
                'movingDurationMinutes' => 0,
                'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(20)]),
                'headcount' => 1,
            ]);
            $this->assertThrows(
                LogicException::class,
                function () use ($fragment): void {
                    DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                        DwsVisitingCareForPwsdChunkImpl::create([
                            'userId' => $this->examples->users[0]->id,
                            'isEmergency' => false,
                            'isFirst' => false,
                            'isBehavioralDisorderSupportCooperation' => false,
                            'providedOn' => Carbon::now(),
                            'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(20)]),
                            'fragments' => Seq::from($fragment),
                        ])
                    );
                }
            );
        });
        $this->specify('サービスが30分未満移動時間が1分以上の場合に LogicException を投げる', function (): void {
            $fragment = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(20)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 2,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(2)]),
                    'headcount' => 1,
                ]),
            ];
            $this->assertThrows(
                LogicException::class,
                function () use ($fragment): void {
                    DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                        DwsVisitingCareForPwsdChunkImpl::create([
                            'userId' => $this->examples->users[0]->id,
                            'isEmergency' => false,
                            'isFirst' => false,
                            'isBehavioralDisorderSupportCooperation' => false,
                            'providedOn' => Carbon::now(),
                            'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(20)]),
                            'fragments' => Seq::fromArray($fragment),
                        ])
                    );
                }
            );
        });
        $this->should('2 サービスの時間数が30分以上60分未満の場合に 1時間の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragment = DwsVisitingCareForPwsdFragment::create([
                'isHospitalized' => false,
                'isLongHospitalized' => false,
                'isCoaching' => false,
                'isMoving' => false,
                'isSecondary' => false,
                'movingDurationMinutes' => 0,
                'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(59)]),
                'headcount' => 1,
            ]);

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(59)]),
                    'fragments' => Seq::from($fragment),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('3 サービスの時間数が60分以上90分未満の場合に 1.5時間の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragment = DwsVisitingCareForPwsdFragment::create([
                'isHospitalized' => false,
                'isLongHospitalized' => false,
                'isCoaching' => false,
                'isMoving' => false,
                'isSecondary' => false,
                'movingDurationMinutes' => 0,
                'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(75)]),
                'headcount' => 1,
            ]);

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(75)]),
                    'fragments' => Seq::from($fragment),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->specify('4 移動時間が20分ある場合に移動の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(60)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => true,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 20,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(20)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(90)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('5 移動時間が30分以上60分未満の場合に1時間の移動の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(60)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => true,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 59,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(59)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(119)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('6 移動時間が75分未満の場合に1.5時間の移動の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(60)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => true,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 90,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(75)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(135)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('7 1人と2人の DwsVisitingCareForPwsdUnit をそれぞれ生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(120)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => true,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now()->addMinutes(60), 'end' => Carbon::now()->addMinutes(120)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(120)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('8 2人と1人の DwsVisitingCareForPwsdFragment がある場合に DwsVisitingCareForPwsdUnit をそれぞれ生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(
                        [
                            'start' => Carbon::create(2021, 6, 10, 0, 0),
                            'end' => Carbon::create(2021, 6, 10, 10, 0),
                        ]
                    ),
                    'headcount' => 2,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(
                        [
                            'start' => Carbon::create(2021, 6, 10, 16, 0),
                            'end' => Carbon::create(2021, 6, 11, 0, 0),
                        ]
                    ),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(
                        [
                            'start' => Carbon::create(2021, 6, 10, 0, 0),
                            'end' => Carbon::create(2021, 6, 11, 0, 0),
                        ]
                    ),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('9 移動時間が181分以上の場合に4時間の移動の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(60)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => true,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 181,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(181)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(241)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('10 180分以上の場合に3.5時間の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(181)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(181)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->specify('11 60分のサービスと1分の移動があった場合に サービスと移動時間がそれぞれ 1時間 の DwsVisitingCareForPwsdUnit を生成する', function (): void {
            $fragments = [
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 1,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(60)]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => false,
                    'isLongHospitalized' => false,
                    'isCoaching' => false,
                    'isMoving' => true,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 1,
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(1)]),
                    'headcount' => 1,
                ]),
            ];

            $actual = DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk(
                DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $this->examples->users[0]->id,
                    'isEmergency' => false,
                    'isFirst' => false,
                    'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                    'isBehavioralDisorderSupportCooperation' => false,
                    'providedOn' => Carbon::now(),
                    'range' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addMinutes(60)]),
                    'fragments' => Seq::fromArray($fragments),
                ])
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('Multiple Units should be generated when Service Fragments is multiple', function (): void {
            $this->markTestSkipped();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot(DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk($this->generateExampleChunk())->toArray());
        });
    }

    /**
     * テスト用のサービス単位を生成する.
     *
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk
     */
    protected function generateExampleChunk(): DwsVisitingCareForPwsdChunk
    {
        return DwsVisitingCareForPwsdChunkImpl::create([
            'userId' => $this->examples->users[0]->id,
            'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
            'isEmergency' => true,
            'isFirst' => true,
            'isBehavioralDisorderSupportCooperation' => true,
            'providedOn' => Carbon::now(),
            'range' => CarbonRange::create([
                'start' => Carbon::now(),
                'end' => Carbon::now()->addMinute(),
            ]),
            'fragments' => Seq::from(
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => true,
                    'isLongHospitalized' => true,
                    'isCoaching' => true,
                    'isMoving' => false,
                    'isSecondary' => true,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create([
                        'start' => Carbon::now(),
                        'end' => Carbon::now()->addHour(),
                    ]),
                    'headcount' => 1,
                ]),
                DwsVisitingCareForPwsdFragment::create([
                    'isHospitalized' => true,
                    'isLongHospitalized' => true,
                    'isCoaching' => true,
                    'isMoving' => true,
                    'isSecondary' => true,
                    'movingDurationMinutes' => 60,
                    'range' => CarbonRange::create([
                        'start' => Carbon::now(),
                        'end' => Carbon::now()->addHour(),
                    ]),
                    'headcount' => 1,
                ])
            ),
        ]);
    }
}

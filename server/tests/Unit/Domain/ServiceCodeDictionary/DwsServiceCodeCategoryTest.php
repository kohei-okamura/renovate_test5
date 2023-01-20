<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsLevel;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\Task;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\RuntimeException;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsServiceCodeCategory} Test.
 */
final class DwsServiceCodeCategoryTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsCertification $certification;
    private DwsCertificationGrant $grant;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->grant = DwsCertificationGrant::create([
                'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                'grantedAmount' => '',
                'activatedOn' => Carbon::create(2020, 1, 1),
                'deactivatedOn' => Carbon::create(2022, 12, 31),
            ]);
            $self->certification = $self->examples->dwsCertifications[0]->copy([
                'dwsLevel' => DwsLevel::level5(),
                'isSubjectOfComprehensiveSupport' => false,
                'activatedOn' => Carbon::create(2020, 1, 1),
                'deactivatedOn' => Carbon::create(2022, 12, 31),
                'grants' => [$self->grant],
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromTask(): void
    {
        $this->specify(
            '与えられた Task に対応するインスタンスを返す',
            function (Task $task, DwsServiceCodeCategory $expected): void {
                $actual = DwsServiceCodeCategory::fromTaskOfHomeHelpService($task);
                $this->assertSame($expected, $actual);
            },
            [
                'examples' => [
                    '身体介護' => [
                        Task::dwsPhysicalCare(),
                        DwsServiceCodeCategory::physicalCare(),
                    ],
                    '家事援助' => [
                        Task::dwsHousework(),
                        DwsServiceCodeCategory::housework(),
                    ],
                    '通院等介助（身体を伴う）' => [
                        Task::dwsAccompanyWithPhysicalCare(),
                        DwsServiceCodeCategory::accompanyWithPhysicalCare(),
                    ],
                    '通院等介助（身体を伴わない）' => [
                        Task::dwsAccompany(),
                        DwsServiceCodeCategory::accompany(),
                    ],
                ],
            ]
        );
        $this->specify('居宅介護以外の Task を与えられた場合は RuntimeException を投げる', function (): void {
            $this->assertThrows(RuntimeException::class, function (): void {
                DwsServiceCodeCategory::fromTaskOfHomeHelpService(Task::dwsVisitingCareForPwsd());
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromDwsProjectServiceCategory(): void
    {
        $this->specify(
            '与えられた DwsProjectServiceCategory に対応するインスタンスを返す',
            function (DwsProjectServiceCategory $category, DwsServiceCodeCategory $expected): void {
                $actual = DwsServiceCodeCategory::fromDwsProjectServiceCategory($category);
                $this->assertSame($expected, $actual);
            },
            [
                'examples' => [
                    '身体介護' => [
                        DwsProjectServiceCategory::physicalCare(),
                        DwsServiceCodeCategory::physicalCare(),
                    ],
                    '家事援助' => [
                        DwsProjectServiceCategory::housework(),
                        DwsServiceCodeCategory::housework(),
                    ],
                    '通院等介助（身体を伴う）' => [
                        DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                        DwsServiceCodeCategory::accompanyWithPhysicalCare(),
                    ],
                    '通院等介助（身体を伴わない）' => [
                        DwsProjectServiceCategory::accompany(),
                        DwsServiceCodeCategory::accompany(),
                    ],
                ],
            ]
        );
        $this->specify(
            '居宅介護以外の DwsProjectServiceCategory を与えられた場合は RuntimeException を投げる',
            function (): void {
                $this->assertThrows(RuntimeException::class, function (): void {
                    DwsServiceCodeCategory::fromDwsProjectServiceCategory(DwsProjectServiceCategory::visitingCareForPwsd());
                });
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromDwsCertification(): void
    {
        $this->specify(
            '受給者証の支給決定内容に従って対応するインスタンスを返す',
            function (DwsServiceCodeCategory $expected, DwsCertificationGrant ...$grants): void {
                $certification = $this->certification->copy([
                    'grants' => $grants,
                ]);

                $actual = DwsServiceCodeCategory::fromDwsCertification($certification, Carbon::create(2021, 1));

                $this->assertEquals($expected, $actual);
            },
            [
                'examples' => [
                    'サービス種別が重度訪問介護（重度障害者等包括支援対象者）の場合' => [
                        DwsServiceCodeCategory::visitingCareForPwsd1(),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1(),
                        ]),
                    ],
                    'サービス種別が重度訪問介護（障害支援区分6該当者）の場合' => [
                        DwsServiceCodeCategory::visitingCareForPwsd2(),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                        ]),
                    ],
                    'サービス種別が重度訪問介護（その他）の場合' => [
                        DwsServiceCodeCategory::visitingCareForPwsd3(),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        ]),
                    ],
                    '複数のサービス種別が含まれている場合' => [
                        DwsServiceCodeCategory::visitingCareForPwsd2(),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                            'grantedAmount' => '',
                            'activatedOn' => Carbon::create(2019, 1, 1),
                            'deactivatedOn' => Carbon::create(2020, 12, 31),
                        ]),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                            'grantedAmount' => '',
                            'activatedOn' => Carbon::create(2021, 1, 1),
                            'deactivatedOn' => Carbon::create(2022, 12, 31),
                        ]),
                    ],
                    '支給決定期間が1日からではない場合' => [
                        DwsServiceCodeCategory::visitingCareForPwsd2(),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                            'grantedAmount' => '',
                            'activatedOn' => Carbon::create(2021, 1, 3),
                            'deactivatedOn' => Carbon::create(2022, 12, 31),
                        ]),
                    ],
                ],
            ]
        );
        $this->specify(
            '支給決定内容に重度訪問介護が含まれない場合は RuntimeException を投げる',
            function (): void {
                $this->assertThrows(RuntimeException::class, function (): void {
                    $certification = $this->certification->copy([
                        'grants' => [
                            $this->grant->copy([
                                'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                            ]),
                        ],
                    ]);
                    DwsServiceCodeCategory::fromDwsCertification($certification, Carbon::create(2021, 1));
                });
            }
        );
        $this->specify(
            'サービス提供年月に対応する支給決定内容がない場合は RuntimeException を投げる',
            function (): void {
                $this->assertThrows(RuntimeException::class, function (): void {
                    $certification = $this->certification->copy([
                        'grants' => [
                            $this->grant->copy([
                                'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                                'grantedAmount' => '',
                                'activatedOn' => Carbon::create(2020, 1, 1),
                                'deactivatedOn' => Carbon::create(2020, 12, 31),
                            ]),
                        ],
                    ]);
                    DwsServiceCodeCategory::fromDwsCertification($certification, Carbon::create(2021, 1));
                });
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_getDayBoundary(): void
    {
        $this->describe('一連のサービスの最初ではない場合', function (): void {
            $this->specify('家事の23時スタートの場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:00:00');
                $actual = DwsServiceCodeCategory::housework()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::none(),
                        isFirst: false
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
            $this->specify('家事の23時45分スタートの場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:45:00');
                $actual = DwsServiceCodeCategory::housework()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::beginner(),
                        isFirst: false
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
            $this->specify('身体介護の23時45分スタートの場合に1日の区切り位置が翌日の0時15分を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:45:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::none(),
                        isFirst: false
                    );
                $this->assertTrue($day->addDay()->startOfDay()->addMinutes(15)->eq($actual));
            });
            $this->specify('身体介護の23時30分スタートの場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:30:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::none(),
                        isFirst: false
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
            $this->specify('身体介護の23時30分スタートかつ重研の場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:30:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                        isFirst: false
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
        });
        $this->describe('一連のサービスの最初の場合', function (): void {
            $this->specify('家事の23時スタートの場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:00:00');
                $actual = DwsServiceCodeCategory::housework()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::none(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
            $this->specify('家事の23時45分スタートの場合に1日の区切り位置が翌日の0時15分を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:45:00');
                $actual = DwsServiceCodeCategory::housework()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::beginner(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->addMinutes(15)->eq($actual));
            });
            $this->specify('身体介護の23時45分スタートの場合に1日の区切り位置が翌日の0時15分を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:45:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::none(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->addMinutes(15)->eq($actual));
            });
            $this->specify('身体介護の23時30分スタートの場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:30:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::none(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
            $this->specify('身体介護の23時30分スタートかつ重研の場合に1日の区切り位置が翌日の0時30分を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:30:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->addMinutes(30)->eq($actual));
            });
            $this->specify('身体介護の22時15分スタートかつ重研の場合に1日の区切り位置が翌日の0時15分を返す', function (): void {
                $day = Carbon::parse('2022-04-01 22:15:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->addMinutes(15)->eq($actual));
            });
            $this->specify('身体介護の22時30分スタートかつ重研の場合に1日の区切り位置が翌日の0時を返す', function (): void {
                $day = Carbon::parse('2022-04-01 23:00:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->eq($actual));
            });
            $this->specify('身体介護の22時45分スタートかつ重研の場合に1日の区切り位置が翌日の0時15分を返す', function (): void {
                $day = Carbon::parse('2022-04-01 22:45:00');
                $actual = DwsServiceCodeCategory::physicalCare()
                    ->getDayBoundary(
                        $day,
                        DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
                        isFirst: true
                    );
                $this->assertTrue($day->addDay()->startOfDay()->addMinutes(15)->eq($actual));
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getMinDurationMinutes(): void
    {
        $this->specify(
            '「身体介護」「通院・身体あり」「通院・身体なし」で提供者区分なしかつ最初のサービスではない場合に 30 を返す',
            function (DwsServiceCodeCategory $category): void {
                $this->assertSame(
                    30,
                    $category->getMinDurationMinutes(DwsHomeHelpServiceProviderType::none(), isFirst: false)
                );
            },
            [
                'examples' => [
                    'physicalCare' => [DwsServiceCodeCategory::physicalCare()],
                    'accompanyWithPhysicalCare' => [DwsServiceCodeCategory::accompanyWithPhysicalCare()],
                    'accompany' => [DwsServiceCodeCategory::accompany()],
                ],
            ]
        );
        $this->specify(
            '「身体介護」「通院・身体あり」で重研かつ最初のサービスの場合に 60 を返す',
            function (DwsServiceCodeCategory $category): void {
                $this->assertSame(
                    60,
                    $category->getMinDurationMinutes(DwsHomeHelpServiceProviderType::careWorkerForPwsd(), isFirst: true)
                );
            },
            [
                'examples' => [
                    'physicalCare' => [DwsServiceCodeCategory::physicalCare()],
                    'accompanyWithPhysicalCare' => [DwsServiceCodeCategory::accompanyWithPhysicalCare()],
                ],
            ]
        );
        $this->should('家事援助の最初のサービスではない場合に 15 を返す', function (): void {
            $this->assertSame(
                15,
                DwsServiceCodeCategory::housework()->getMinDurationMinutes(DwsHomeHelpServiceProviderType::none(), isFirst: false)
            );
        });
        $this->should('家事援助の最初のサービスではないかつ重研場合に 15 を返す', function (): void {
            $this->assertSame(
                15,
                DwsServiceCodeCategory::housework()
                    ->getMinDurationMinutes(DwsHomeHelpServiceProviderType::careWorkerForPwsd(), isFirst: false)
            );
        });
        $this->specify('家事援助の最初のサービスかつ重研の場合に 30 を返す', function (): void {
            $this->assertSame(
                30,
                DwsServiceCodeCategory::housework()
                    ->getMinDurationMinutes(DwsHomeHelpServiceProviderType::none(), isFirst: true)
            );
        });
        $this->should('重度訪問介護の場合に LogicException を投げる', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                DwsServiceCodeCategory::visitingCareForPwsd1()->getMinDurationMinutes(DwsHomeHelpServiceProviderType::none(), isFirst: false);
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isHomeHelpService(): void
    {
        $this->should(
            'return true',
            function (DwsServiceCodeCategory $category): void {
                $this->assertTrue($category->isHomeHelpService());
            },
            [
                'examples' => [
                    'physicalCare' => [DwsServiceCodeCategory::physicalCare()],
                    'accompanyWithPhysicalCare' => [DwsServiceCodeCategory::accompanyWithPhysicalCare()],
                    'accompany' => [DwsServiceCodeCategory::accompany()],
                    'housework' => [DwsServiceCodeCategory::housework()],
                    'accessibleTaxi' => [DwsServiceCodeCategory::accessibleTaxi()],
                ],
            ]
        );
        $this->should(
            'return false',
            function (DwsServiceCodeCategory $category): void {
                $this->assertFalse($category->isHomeHelpService());
            },
            [
                'examples' => [
                    'visitingCareForPwsd1' => [DwsServiceCodeCategory::visitingCareForPwsd1()],
                    'visitingCareForPwsd2' => [DwsServiceCodeCategory::visitingCareForPwsd2()],
                    'visitingCareForPwsd3' => [DwsServiceCodeCategory::visitingCareForPwsd3()],
                    'outingSupportForPwsd' => [DwsServiceCodeCategory::outingSupportForPwsd()],
                    'specifiedOfficeAddition1' => [DwsServiceCodeCategory::specifiedOfficeAddition1()],
                    'specifiedOfficeAddition2' => [DwsServiceCodeCategory::specifiedOfficeAddition2()],
                    'specifiedOfficeAddition3' => [DwsServiceCodeCategory::specifiedOfficeAddition3()],
                    'specifiedOfficeAddition4' => [DwsServiceCodeCategory::specifiedOfficeAddition4()],
                    'specifiedAreaAddition' => [DwsServiceCodeCategory::specifiedAreaAddition()],
                    'emergencyAddition1' => [DwsServiceCodeCategory::emergencyAddition1()],
                    'emergencyAddition2' => [DwsServiceCodeCategory::emergencyAddition2()],
                    'suckingSupportSystemAddition' => [DwsServiceCodeCategory::suckingSupportSystemAddition()],
                    'firstTimeAddition' => [DwsServiceCodeCategory::firstTimeAddition()],
                    'copayCoordinationAddition' => [DwsServiceCodeCategory::copayCoordinationAddition()],
                    'welfareSpecialistCooperationAddition' => [DwsServiceCodeCategory::welfareSpecialistCooperationAddition()],
                    'behavioralDisorderSupportCooperationAddition' => [DwsServiceCodeCategory::behavioralDisorderSupportCooperationAddition()],
                    'treatmentImprovementAddition1' => [DwsServiceCodeCategory::treatmentImprovementAddition1()],
                    'treatmentImprovementAddition2' => [DwsServiceCodeCategory::treatmentImprovementAddition2()],
                    'treatmentImprovementAddition3' => [DwsServiceCodeCategory::treatmentImprovementAddition3()],
                    'treatmentImprovementAddition4' => [DwsServiceCodeCategory::treatmentImprovementAddition4()],
                    'treatmentImprovementAddition5' => [DwsServiceCodeCategory::treatmentImprovementAddition5()],
                    'treatmentImprovementSpecialAddition' => [DwsServiceCodeCategory::treatmentImprovementSpecialAddition()],
                    'specifiedTreatmentImprovementAddition1' => [DwsServiceCodeCategory::specifiedTreatmentImprovementAddition1()],
                    'specifiedTreatmentImprovementAddition2' => [DwsServiceCodeCategory::specifiedTreatmentImprovementAddition2()],
                    'covid19PandemicSpecialAddition' => [DwsServiceCodeCategory::covid19PandemicSpecialAddition()],
                    'bulkServiceSubtraction1' => [DwsServiceCodeCategory::bulkServiceSubtraction1()],
                    'bulkServiceSubtraction2' => [DwsServiceCodeCategory::bulkServiceSubtraction2()],
                    'physicalRestraintSubtraction' => [DwsServiceCodeCategory::physicalRestraintSubtraction()],
                    'movingCareSupportAddition' => [DwsServiceCodeCategory::movingCareSupportAddition()],
                ],
            ]
        );
    }
}

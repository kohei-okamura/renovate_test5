<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsLevel;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsCertificationAgreementTypeDwsLevelRule} のテスト.
 */
final class DwsCertificationAgreementTypeDwsLevelRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    private DwsCertification $dwsCertification;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsCertification = $self->examples->dwsCertifications[0];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsCertificationAgreementTypeDwsLevelRule(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return $this->buildCustomValidator(
                $dataOverwrite + [
                    'effectivatedOn' => $this->dwsCertification->effectivatedOn->toDateString(),
                    'agreements' => [
                        [
                            'expiredOn' => $this->dwsCertification->agreements[0]->expiredOn->toDateString(),
                            'dwsCertificationAgreementType' => $this->dwsCertification->agreements[0]->dwsCertificationAgreementType->value(),
                        ],
                    ],
                    'dwsLevel' => $this->dwsCertification->dwsLevel->value(),
                    'isSubjectOfComprehensiveSupport' => $this->dwsCertification->isSubjectOfComprehensiveSupport,
                ],
                ['agreements.*.dwsCertificationAgreementType' => 'dws_certification_agreement_type_dws_level:effectivatedOn,agreements.*.expiredOn,dwsLevel,isSubjectOfComprehensiveSupport']
            );
        };
        $this->should('pass when effectivatedOn is not date', function () use ($customValidator): void {
            $agreements = [
                [
                    'expiredOn' => $this->dwsCertification->agreements[0]->expiredOn->toDateString(),
                    'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                ],
            ];
            $dwsLevel = DwsLevel::level6()->value();
            $isSubjectOfComprehensiveSupport = true;
            $effectivatedOn = 'error';

            $this->assertTrue(
                $customValidator(
                    compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                )->passes()
            );
        });
        $this->should('pass when expiredOn is not date', function () use ($customValidator): void {
            $agreements = [
                [
                    'expiredOn' => 'error',
                    'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                ],
            ];
            $dwsLevel = DwsLevel::level6()->value();
            $isSubjectOfComprehensiveSupport = true;

            $this->assertTrue(
                $customValidator(
                    compact('agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                )->passes()
            );
        });
        $this->should('pass when dwsLevel is invalid', function () use ($customValidator): void {
            $agreements = [
                [
                    'expiredOn' => $this->dwsCertification->agreements[0]->expiredOn->toDateString(),
                    'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                ],
            ];
            $dwsLevel = self::INVALID_ENUM_VALUE;
            $isSubjectOfComprehensiveSupport = true;

            $this->assertTrue(
                $customValidator(
                    compact('agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                )->passes()
            );
        });
        $this->should('pass when dwsCertificationAgreementType is invalid', function () use ($customValidator): void {
            $agreements = [
                [
                    'expiredOn' => $this->dwsCertification->agreements[0]->expiredOn->toDateString(),
                    'dwsCertificationAgreementType' => self::INVALID_ENUM_VALUE,
                ],
            ];
            $dwsLevel = DwsLevel::level6()->value();
            $isSubjectOfComprehensiveSupport = true;

            $this->assertTrue(
                $customValidator(
                    compact('agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                )->passes()
            );
        });
        $this->should('pass when expiredOn is before effectivatedOn', function () use ($customValidator): void {
            $agreements = [
                [
                    'expiredOn' => Carbon::now()->toDateString(),
                    'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                ],
            ];
            $dwsLevel = DwsLevel::level6()->value();
            $isSubjectOfComprehensiveSupport = true;
            $effectivatedOn = Carbon::now()->addDay()->toDateString();

            $this->assertTrue(
                $customValidator(
                    compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                )->passes()
            );
        });
        $this->should(
            'pass when DwsCertificationAgreementType is visitingCareForPwsd1 and DwsLevel is level6 and isSubjectOfComprehensiveSupport is true',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level6()->value();
                $isSubjectOfComprehensiveSupport = true;
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                    )->passes()
                );
            }
        );
        $this->should(
            'fail when DwsLevel is not level6 although DwsCertificationAgreementType is visitingCareForPwsd1 and isSubjectOfComprehensiveSupport is true',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level1()->value();
                $isSubjectOfComprehensiveSupport = true;
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                    )->fails()
                );
            }
        );
        $this->should(
            'fail when isSubjectOfComprehensiveSupport is false although DwsCertificationAgreementType is visitingCareForPwsd1 and DwsLevel is level6 ',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level6()->value();
                $isSubjectOfComprehensiveSupport = false;
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                    )->fails()
                );
            }
        );
        $this->should(
            'pass when DwsCertificationAgreementType is visitingCareForPwsd2 and DwsLevel is level6 and isSubjectOfComprehensiveSupport is false',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd2()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level6()->value();
                $isSubjectOfComprehensiveSupport = false;
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                    )->passes()
                );
            }
        );
        $this->should(
            'fail when DwsLevel is not level6 although DwsCertificationAgreementType is visitingCareForPwsd2 and isSubjectOfComprehensiveSupport is false',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd2()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level1()->value();
                $isSubjectOfComprehensiveSupport = false;
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                    )->fails()
                );
            }
        );
        $this->should(
            'fail when isSubjectOfComprehensiveSupport is true although DwsCertificationAgreementType is visitingCareForPwsd2 and DwsLevel is level6 ',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd2()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level6()->value();
                $isSubjectOfComprehensiveSupport = true;
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                    )->fails()
                );
            }
        );
        $this->describe('dwsCertificationAgreementType が 重度訪問介護（その他）の場合', function () use ($customValidator) {
            $this->should(
                'DwsLevel が level6 かつ isSubjectOfComprehensiveSupport が true の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level6()->value();
                    $isSubjectOfComprehensiveSupport = true;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level6 かつ isSubjectOfComprehensiveSupport が false の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level6()->value();
                    $isSubjectOfComprehensiveSupport = false;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level5 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level5()->value();
                    $isSubjectOfComprehensiveSupport = false;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level4 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level4()->value();
                    $isSubjectOfComprehensiveSupport = false;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level3 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level3()->value();
                    $isSubjectOfComprehensiveSupport = false;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
            $this->specify(
                'DwsLevel が level2 のときにバリデーションが失敗する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level2()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->fails()
                    );
                }
            );
            $this->specify(
                'DwsLevel が level1 のときにバリデーションが失敗する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level1()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->fails()
                    );
                }
            );
        });
        $this->describe('dwsCertificationAgreementType が 重度訪問介護（移動加算）の場合', function () use ($customValidator) {
            $this->should(
                'DwsLevel が level6 かつ isSubjectOfComprehensiveSupport が true の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level6()->value();
                    $isSubjectOfComprehensiveSupport = true;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level6 かつ isSubjectOfComprehensiveSupport が false の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level6()->value();
                    $isSubjectOfComprehensiveSupport = false;
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel', 'isSubjectOfComprehensiveSupport')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level5 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level5()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level4 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level4()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level3 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level3()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level2 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level3()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
            $this->should(
                'DwsLevel が level1 の場合にバリデーションを通過する',
                function () use ($customValidator): void {
                    $agreements = [
                        [
                            'expiredOn' => '',
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd()->value(),
                        ],
                    ];
                    $dwsLevel = DwsLevel::level3()->value();
                    $effectivatedOn = Carbon::now()->subDay()->toDateString();

                    $this->assertTrue(
                        $customValidator(
                            compact('effectivatedOn', 'agreements', 'dwsLevel')
                        )->passes()
                    );
                }
            );
        });
        $this->should(
            'DwsCertificationAgreementType が 重度訪問介護以外の場合にバリデーションを通過する',
            function () use ($customValidator): void {
                $agreements = [
                    [
                        'expiredOn' => '',
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare()->value(),
                    ],
                ];
                $dwsLevel = DwsLevel::level1()->value();
                $effectivatedOn = Carbon::now()->subDay()->toDateString();

                $this->assertTrue(
                    $customValidator(
                        compact('effectivatedOn', 'agreements', 'dwsLevel')
                    )->passes()
                );
            }
        );
    }
}

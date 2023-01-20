<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsLevel;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsCertificationGrantExclusiveRule} のテスト.
 */
final class DwsCertificationGrantDoesNotContradictRuleTest extends Test
{
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsCertificationGrantExclusive(): void
    {
        $examples = [
            '区分6' => [DwsLevel::level6()],
            '区分5' => [DwsLevel::level5()],
            '区分4' => [DwsLevel::level4()],
            '区分3' => [DwsLevel::level3()],
            '区分2' => [DwsLevel::level2()],
            '区分1' => [DwsLevel::level1()],
            '非該当' => [DwsLevel::notApplicable()],
        ];
        $this->specify(
            '入力値が配列ではない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    grant: 'This is not an array'
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            'サービス種別が入力されていない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    grant: [
                        'deactivatedOn' => '2022-12-31',
                    ]
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            'サービス種別が正常な区分値でない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    grant: [
                        'dwsCertificationServiceType' => -1,
                        'deactivatedOn' => '2022-12-31',
                    ]
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            '支給決定期間の終了日が入力されていない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    grant: [
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                    ]
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            '支給決定期間の終了日が正常な日付でない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    grant: [
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                        'deactivatedOn' => 'This is not a valid date string',
                    ]
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            '適用日が入力されていない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    effectivatedOn: ''
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            '適用日が正常な日付でない場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    effectivatedOn: 'This is not a valid date string'
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            '支給決定期間の終了日が適用日より前（過去）である場合は通過する',
            function (DwsLevel $level): void {
                $validator = $this->buildValidator(
                    level: $level->value(),
                    effectivatedOn: '2023-01-01',
                    grant: [
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                        'deactivatedOn' => '2022-12-31',
                    ]
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
        $this->specify(
            '障害支援区分が入力されていない場合は通過する',
            function (): void {
                $validator = $this->buildValidator(
                    level: '',
                );
                $this->assertTrue($validator->passes());
            }
        );
        $this->specify(
            '障害支援区分が正常な区分値でない場合は通過する',
            function (): void {
                $validator = $this->buildValidator(
                    level: -1,
                );
                $this->assertTrue($validator->passes());
            }
        );
        $this->describe('重度訪問介護（重度障害者等包括支援対象者）の場合', function (): void {
            $this->specify(
                '障害支援区分が区分6以外の場合は通過しない',
                function (DwsLevel $level): void {
                    $validator = $this->buildValidator(
                        level: $level->value(),
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->fails());
                },
                [
                    'examples' => [
                        '非該当' => [DwsLevel::notApplicable()],
                        '区分1' => [DwsLevel::level1()],
                        '区分2' => [DwsLevel::level2()],
                        '区分3' => [DwsLevel::level3()],
                        '区分4' => [DwsLevel::level4()],
                        '区分5' => [DwsLevel::level5()],
                    ],
                ]
            );
            $this->specify(
                '障害支援区分が区分6であっても重度障害者等包括支援対象でない場合は通過しない',
                function (): void {
                    $validator = $this->buildValidator(
                        level: DwsLevel::level6()->value(),
                        isSubjectOfComprehensiveSupport: false,
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->fails());
                }
            );
            $this->specify(
                '障害支援区分が区分6かつ重度障害者等包括支援対象の場合は通過する',
                function (): void {
                    $validator = $this->buildValidator(
                        level: DwsLevel::level6()->value(),
                        isSubjectOfComprehensiveSupport: true,
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->passes());
                }
            );
        });
        $this->describe('重度訪問介護（障害支援区分6該当者）の場合', function (): void {
            $this->specify(
                '障害支援区分が区分6以外の場合は通過しない',
                function (DwsLevel $level): void {
                    $validator = $this->buildValidator(
                        level: $level->value(),
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->fails());
                },
                [
                    'examples' => [
                        '非該当' => [DwsLevel::notApplicable()],
                        '区分1' => [DwsLevel::level1()],
                        '区分2' => [DwsLevel::level2()],
                        '区分3' => [DwsLevel::level3()],
                        '区分4' => [DwsLevel::level4()],
                        '区分5' => [DwsLevel::level5()],
                    ],
                ]
            );
            $this->specify(
                '障害支援区分が区分6であれば重度障害者等包括支援対象でない場合でも通過する',
                function (): void {
                    $validator = $this->buildValidator(
                        level: DwsLevel::level6()->value(),
                        isSubjectOfComprehensiveSupport: false,
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->passes());
                }
            );
            $this->specify(
                '障害支援区分が区分6かつ重度障害者等包括支援対象の場合は通過する',
                function (): void {
                    $validator = $this->buildValidator(
                        level: DwsLevel::level6()->value(),
                        isSubjectOfComprehensiveSupport: true,
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->passes());
                }
            );
        });
        $this->describe('重度訪問介護（その他）の場合', function (): void {
            $this->specify(
                '障害支援区分が「非該当」「区分1」「区分2」の場合は通過しない',
                function (DwsLevel $level): void {
                    $validator = $this->buildValidator(
                        level: $level->value(),
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->fails());
                },
                [
                    'examples' => [
                        '非該当' => [DwsLevel::notApplicable()],
                        '区分1' => [DwsLevel::level1()],
                        '区分2' => [DwsLevel::level2()],
                    ],
                ]
            );
            $this->specify(
                '障害支援区分が「区分3」「区分4」「区分5」「区分6」の場合は通過する',
                function (DwsLevel $level, bool $isSubjectOfComprehensiveSupport): void {
                    $validator = $this->buildValidator(
                        level: $level->value(),
                        isSubjectOfComprehensiveSupport: $isSubjectOfComprehensiveSupport,
                        grant: [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3()->value(),
                            'deactivatedOn' => '2022-12-31',
                        ]
                    );
                    $this->assertTrue($validator->passes());
                },
                [
                    'examples' => [
                        '区分3' => [DwsLevel::level3(), false],
                        '区分4' => [DwsLevel::level4(), false],
                        '区分5' => [DwsLevel::level5(), false],
                        '区分6' => [DwsLevel::level6(), false],
                        '区分6・重度障害者等包括支援対象' => [DwsLevel::level6(), true],
                    ],
                ]
            );
        });
        $this->describe('重度訪問介護以外の場合', function (): void {
            $this->specify(
                '障害支援区分および重度障害者等包括支援対象の内容に関わらず常に通過する',
                function (DwsLevel $level, bool $isSubjectOfComprehensiveSupport): void {
                    $data = [
                        'effectivatedOn' => '2022-06-04',
                        'dwsLevel' => $level->value(),
                        'isSubjectOfComprehensiveSupport' => $isSubjectOfComprehensiveSupport,
                        'grants' => [
                            [
                                'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare()->value(),
                                'deactivatedOn' => '2022-12-31',
                            ],
                            [
                                'dwsCertificationServiceType' => DwsCertificationServiceType::housework()->value(),
                                'deactivatedOn' => '2022-12-31',
                            ],
                            [
                                'dwsCertificationServiceType' => DwsCertificationServiceType::accompanyWithPhysicalCare()->value(),
                                'deactivatedOn' => '2022-12-31',
                            ],
                            [
                                'dwsCertificationServiceType' => DwsCertificationServiceType::accompany()->value(),
                                'deactivatedOn' => '2022-12-31',
                            ],
                        ],
                    ];
                    $rule = [
                        'grants.*' => 'dws_certification_grant_exclusive:effectivatedOn,dwsLevel,isSubjectOfComprehensiveSupport',
                    ];
                    $validator = $this->buildCustomValidator($data, $rule);

                    $this->assertTrue($validator->passes());
                },
                [
                    'examples' => [
                        '非該当' => [DwsLevel::notApplicable(), false],
                        '区分1' => [DwsLevel::level1(), false],
                        '区分2' => [DwsLevel::level2(), false],
                        '区分3' => [DwsLevel::level3(), false],
                        '区分4' => [DwsLevel::level4(), false],
                        '区分5' => [DwsLevel::level5(), false],
                        '区分6' => [DwsLevel::level6(), false],
                        '区分6・重度障害者等包括支援対象' => [DwsLevel::level6(), true],
                    ],
                ]
            );
        });
    }

    /**
     * テスト用の {@link \App\Validations\CustomValidator} のインスタンスを生成する.
     *
     * @param mixed $level
     * @param mixed $effectivatedOn
     * @param false|mixed $isSubjectOfComprehensiveSupport
     * @param mixed $grant
     * @return \App\Validations\CustomValidator
     */
    private function buildValidator(
        mixed $level,
        mixed $effectivatedOn = '2022-06-04',
        mixed $isSubjectOfComprehensiveSupport = false,
        mixed $grant = null,
    ): CustomValidator {
        $data = [
            'effectivatedOn' => $effectivatedOn,
            'dwsLevel' => $level,
            'isSubjectOfComprehensiveSupport' => $isSubjectOfComprehensiveSupport,
            'grants' => [
                $grant ?? [
                    'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                    'deactivatedOn' => '2022-12-31',
                ],
            ],
        ];
        $rule = [
            'grants.*' => 'dws_certification_grant_exclusive:effectivatedOn,dwsLevel,isSubjectOfComprehensiveSupport',
        ];
        return $this->buildCustomValidator($data, $rule);
    }
}

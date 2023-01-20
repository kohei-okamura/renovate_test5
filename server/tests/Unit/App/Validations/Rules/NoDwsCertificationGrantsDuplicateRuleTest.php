<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\DwsCertification\DwsCertificationServiceType;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NoDwsCertificationGrantsDuplicateRule} のテスト.
 */
final class NoDwsCertificationGrantsDuplicateRuleTest extends Test
{
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNoDwsCertificationGrantsDuplicate(): void
    {
        $this->specify('空の場合はバリデーションを通過する', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'grants' => [],
                ],
                ['grants.*' => 'no_dws_certification_grants_duplicate:grants'],
            );

            $this->assertTrue($validator->passes());
        });
        $this->specify('支給決定内容のサービス種別が重訪ではない場合はバリデーションを通過する', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'grants' => [
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                    ],
                ],
                ['grants.*' => 'no_dws_certification_grants_duplicate:grants'],
            );

            $this->assertTrue($validator->passes());
        });
        // 通常こんなテストはいらないが、一つ目が重訪以外の場合のバグがあったのでテストをしている.
        $this->specify('支給決定内容に重訪が複数存在しないかつ一つ目が重訪以外の場合はバリデーションを通過する', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'grants' => [
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::housework()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                    ],
                ],
                ['grants.*' => 'no_dws_certification_grants_duplicate:grants'],
            );

            $this->assertTrue($validator->passes());
        });
        $this->specify('支給決定内容に重訪が複数存在しない場合はバリデーションを通過する', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'grants' => [
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::housework()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                    ],
                ],
                ['grants.*' => 'no_dws_certification_grants_duplicate:grants'],
            );

            $this->assertTrue($validator->passes());
        });
        $this->specify('支給決定内容に複数重訪が存在するが期間は重複しない場合はバリデーションを通過する', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'grants' => [
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2021-01-01',
                            'deactivatedOn' => '2021-12-31',
                        ],
                    ],
                ],
                ['grants.*' => 'no_dws_certification_grants_duplicate:grants'],
            );

            $this->assertTrue($validator->passes());
        });
        $this->specify('支給決定内容に複数重訪が存在し期間が重複する場合はバリデーションを通過しない', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'grants' => [
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-31',
                        ],
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                            'grantedAmount' => 100,
                            'activatedOn' => '2020-12-31',
                            'deactivatedOn' => '2021-12-31',
                        ],
                    ],
                ],
                ['grants.*' => 'no_dws_certification_grants_duplicate:grants'],
            );

            $this->assertTrue($validator->fails());
        });
    }
}

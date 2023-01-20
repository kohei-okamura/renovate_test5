<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\DwsCertification;

use Domain\DwsCertification\DwsCertificationServiceType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\DwsCertification\DwsCertificationServiceType} のテスト.
 */
final class DwsCertificationServiceTypeTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isHomeHelpService(): void
    {
        $this->should(
            'return true when this is home help service',
            function (DwsCertificationServiceType $x): void {
                $this->assertTrue($x->isHomeHelpService());
            },
            [
                'examples' => [
                    'physicalCare' => [
                        DwsCertificationServiceType::physicalCare(),
                    ],
                    'housework' => [
                        DwsCertificationServiceType::housework(),
                    ],
                    'accompanyWithPhysicalCare' => [
                        DwsCertificationServiceType::accompanyWithPhysicalCare(),
                    ],
                    'accompany' => [
                        DwsCertificationServiceType::accompany(),
                    ],
                ],
            ]
        );
        $this->should(
            'return true when this is visiting care for pwsd',
            function (DwsCertificationServiceType $x): void {
                $this->assertFalse($x->isHomeHelpService());
            },
            [
                'examples' => [
                    'visitingCareForPwsd1' => [
                        DwsCertificationServiceType::visitingCareForPwsd1(),
                    ],
                    'visitingCareForPwsd2' => [
                        DwsCertificationServiceType::visitingCareForPwsd2(),
                    ],
                    'visitingCareForPwsd3' => [
                        DwsCertificationServiceType::visitingCareForPwsd3(),
                    ],
                ],
            ]
        );
    }
}

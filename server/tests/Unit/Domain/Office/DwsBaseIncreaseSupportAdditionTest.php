<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\DwsBaseIncreaseSupportAddition} のテスト.
 */
final class DwsBaseIncreaseSupportAdditionTest extends Test
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
    public function describe_compute(): void
    {
        $this->should(
            'compute after 2022-10',
            function (
                DwsBaseIncreaseSupportAddition $self,
                DwsServiceDivisionCode $serviceDivisionCode,
                int $expect
            ): void {
                $actual = $self->compute(1000, $serviceDivisionCode, Carbon::create(2022, 10));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1 and visitingCareForPwsd' => [
                    DwsBaseIncreaseSupportAddition::addition1(), DwsServiceDivisionCode::visitingCareForPwsd(), 45,
                ],
                'with addition1 and homeHelpService' => [
                    DwsBaseIncreaseSupportAddition::addition1(), DwsServiceDivisionCode::homeHelpService(), 45,
                ],
            ]]
        );

        $this->should('not compute before 2022-10', function (): void {
            $self = DwsBaseIncreaseSupportAddition::addition1();
            $actual = $self->compute(1000, DwsServiceDivisionCode::visitingCareForPwsd(), Carbon::create(2022, 9));
            $this->assertEmpty($actual);
        });
    }
}

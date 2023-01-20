<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\Carbon;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition} のテスト.
 */
final class VisitingCareForPwsdSpecifiedOfficeAdditionTest extends Test
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
            'compute after 2019-10',
            function (
                VisitingCareForPwsdSpecifiedOfficeAddition $self,
                int $expect
            ): void {
                $actual = $self->compute(1000, Carbon::create(2019, 10));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1' => [
                    VisitingCareForPwsdSpecifiedOfficeAddition::addition1(), 200,
                ],
                'with addition2' => [
                    VisitingCareForPwsdSpecifiedOfficeAddition::addition2(), 100,
                ],
                'with addition3' => [
                    VisitingCareForPwsdSpecifiedOfficeAddition::addition3(), 100,
                ],
            ]]
        );
        $this->should('not compute before 2019-10', function (): void {
            $self = VisitingCareForPwsdSpecifiedOfficeAddition::addition1();
            $actual = $self->compute(
                1000,
                Carbon::create(2019, 9)
            );
            $this->assertEmpty($actual);
        });
    }
}

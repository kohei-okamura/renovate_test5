<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Decimal;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Common\Decimal} のテスト.
 */
final class DecimalTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_fromInt(): void
    {
        $this->should('should return an instance', function (): void {
            $actual = Decimal::fromInt(123456_78, 2);

            $this->assertSame($actual->toInt(0), 123456);
            $this->assertSame($actual->toInt(1), 1234567);
            $this->assertSame($actual->toInt(2), 12345678);
            $this->assertSame($actual->toInt(3), 123456780);
            $this->assertSame($actual->toInt(4), 1234567800);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toInt(): void
    {
        $this->should('should return an int representation', function (): void {
            $actual = Decimal::fromInt(11_200, 3);

            $this->assertSame($actual->toInt(0), 11);
            $this->assertSame($actual->toInt(1), 112);
            $this->assertSame($actual->toInt(2), 1120);
            $this->assertSame($actual->toInt(3), 11200);
            $this->assertSame($actual->toInt(4), 112000);
        });
    }
}

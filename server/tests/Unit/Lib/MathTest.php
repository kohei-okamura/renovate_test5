<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Lib;

use Lib\Math;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Lib\Math} のテスト.
 */
final class MathTest extends Test
{
    use UnitSupport;

    private static array $examples;

    /**
     * @test
     * @return void
     */
    public function describe_ceil(): void
    {
        $this->should('return ceiled value', function (): void {
            $this->assertSame(1945, (int)ceil(1800 * 1.08));
            $this->assertSame(1944, Math::ceil(1800 * 1.08));
            $this->assertSame(100, (int)ceil(100.0));
            $this->assertSame(100, Math::ceil(100.0));
            $this->assertSame(201, (int)ceil(200.0001));
            $this->assertSame(201, Math::ceil(200.0001));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_floor(): void
    {
        $this->should('return floored value', function (): void {
            $this->assertSame(6, (int)floor(1000 * (0.7 / 100)));
            $this->assertSame(7, Math::floor(1000 * (0.7 / 100)));
            $this->assertSame(100, (int)floor(100.0));
            $this->assertSame(100, Math::floor(100.0));
            $this->assertSame(100, (int)floor(100.999));
            $this->assertSame(100, Math::floor(100.999));
            $this->assertSame(200, (int)floor(200.0001));
            $this->assertSame(200, Math::floor(200.0001));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_round(): void
    {
        $this->should('return floored value', function (): void {
            $this->assertSame(0, Math::round(0.10));
            $this->assertSame(0, Math::round(0.20));
            $this->assertSame(0, Math::round(0.30));
            $this->assertSame(0, Math::round(0.40));
            $this->assertSame(0, Math::round(0.49));
            $this->assertSame(1, Math::round(0.50));
            $this->assertSame(1, Math::round(0.60));
            $this->assertSame(1, Math::round(0.70));
            $this->assertSame(1, Math::round(0.80));
            $this->assertSame(1, Math::round(0.90));
        });
    }
}

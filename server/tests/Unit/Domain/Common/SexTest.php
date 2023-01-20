<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Codeception\Test\Unit;
use Domain\Common\Sex;
use Tests\Unit\Helpers\UnitSupport;

/**
 * Sex のテスト.
 */
class SexTest extends Unit
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_values(): void
    {
        $this->should('have 4 items', function (): void {
            $this->assertSame(4, Sex::length());
        });

        $this->should('have expected values', function (): void {
            $this->assertSame(0, Sex::notKnown()->value());
            $this->assertSame(1, Sex::male()->value());
            $this->assertSame(2, Sex::female()->value());
            $this->assertSame(9, Sex::notApplicable()->value());
        });
    }
}

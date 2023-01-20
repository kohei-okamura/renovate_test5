<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Exchange\EndRecord;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\EndRecord} のテスト.
 */
final class EndRecordTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_toArray(): void
    {
        $this->should('should return valid csv values', function (): void {
            $this->assertEquals(
                Csv::read(__DIR__ . '/EndRecordTest.csv')->toArray(),
                [
                    EndRecord::instance()->toArray(12345678),
                    EndRecord::instance()->toArray(20080517),
                    EndRecord::instance()->toArray(19820809),
                ]
            );
        });
    }
}

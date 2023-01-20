<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Common\Carbon;
use Domain\Exchange\LtcsControlRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\LtcsControlRecord} のテスト.
 */
final class LtcsControlRecordTest extends Test
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
                Csv::read(__DIR__ . '/LtcsControlRecordTest.csv')->toArray(),
                Arrays::generate(function (): iterable {
                    foreach ($this->examples() as $recordNumber => $record) {
                        yield $record->toArray($recordNumber);
                    }
                })
            );
        });
    }

    /**
     * Examples.
     *
     * @return array&\Domain\Exchange\LtcsControlRecord[]
     */
    private function examples(): array
    {
        return [
            12345678 => new LtcsControlRecord(
                recordCount: 1582,
                officeCode: '1371214733',
                transactedIn: Carbon::create(2019, 12),
            ),
            20080517 => new LtcsControlRecord(
                recordCount: 2020,
                officeCode: '1372012755',
                transactedIn: Carbon::create(2020, 1),
            ),
            19820809 => new LtcsControlRecord(
                recordCount: 1192,
                officeCode: '1371605575',
                transactedIn: Carbon::create(2020, 2),
            ),
        ];
    }
}

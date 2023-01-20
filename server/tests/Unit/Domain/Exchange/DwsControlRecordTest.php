<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Common\Carbon;
use Domain\Exchange\DwsControlRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsControlRecord} のテスト.
 */
final class DwsControlRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsControlRecordTest.csv')->toArray(),
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
     * @return array
     */
    private function examples(): array
    {
        return [
            12345678 => new DwsControlRecord(
                recordCount: 1582,
                officeCode: '1311401366',
                dataType: 'J11',
                transactedIn: Carbon::create(2019, 12),
            ),
            20080517 => new DwsControlRecord(
                recordCount: 2020,
                officeCode: '1310402167',
                dataType: 'J41',
                transactedIn: Carbon::create(2020, 1),
            ),
            19820809 => new DwsControlRecord(
                recordCount: 1192,
                officeCode: '1311204091',
                dataType: 'J61',
                transactedIn: Carbon::create(2020, 2),
            ),
        ];
    }
}

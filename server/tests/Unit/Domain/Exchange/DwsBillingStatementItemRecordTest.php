<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingStatementItemRecord;
use Domain\ServiceCode\ServiceCode;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingStatementItemRecord} のテスト.
 */
final class DwsBillingStatementItemRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingStatementItemRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingStatementItemRecord[]
     */
    private function examples(): array
    {
        return [
            25 => new DwsBillingStatementItemRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131024',
                officeCode: '1311401366',
                dwsNumber: '0000010884',
                serviceCode: ServiceCode::fromString('116715'),
                unitScore: 505,
                count: 1,
                totalScore: 505,
            ),
            53 => new DwsBillingStatementItemRecord(
                providedIn: Carbon::create(2008, 5),
                cityCode: '131032',
                officeCode: '1311401366',
                dwsNumber: '8000035272',
                serviceCode: ServiceCode::fromString('124221'),
                unitScore: 138,
                count: 136,
                totalScore: 18768,
            ),
            134 => new DwsBillingStatementItemRecord(
                providedIn: Carbon::create(2020, 8),
                cityCode: '131041',
                officeCode: '1311401366',
                dwsNumber: '0000059220',
                serviceCode: ServiceCode::fromString('124121'),
                unitScore: 147,
                count: 102,
                totalScore: 14994,
            ),
        ];
    }
}

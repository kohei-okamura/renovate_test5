<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingStatementContractRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingStatementContractRecord} のテスト.
 */
final class DwsBillingStatementContractRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingStatementContractRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingStatementContractRecord[]
     */
    private function examples(): array
    {
        return [
            27 => new DwsBillingStatementContractRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131041',
                officeCode: '1311401366',
                dwsNumber: '0000010884',
                dwsGrantedServiceCode: DwsGrantedServiceCode::housework(),
                grantedAmount: 600,
                agreedOn: Carbon::create(2019, 2, 1),
                expiredOn: null,
                indexNumber: 1,
            ),
            334 => new DwsBillingStatementContractRecord(
                providedIn: Carbon::create(2020, 4),
                cityCode: '131083',
                officeCode: '1311401366',
                dwsNumber: '1310890544',
                dwsGrantedServiceCode: DwsGrantedServiceCode::visitingCareForPwsd1(),
                grantedAmount: 29460,
                agreedOn: Carbon::create(2019, 7, 1),
                expiredOn: Carbon::create(2019, 12, 31),
                indexNumber: 1,
            ),
            706 => new DwsBillingStatementContractRecord(
                providedIn: Carbon::create(2008, 5),
                cityCode: '131148',
                officeCode: '1311401366',
                dwsNumber: '3000025746',
                dwsGrantedServiceCode: DwsGrantedServiceCode::visitingCareForPwsd1(),
                grantedAmount: 3600,
                agreedOn: Carbon::create(2019, 10, 1),
                expiredOn: null,
                indexNumber: 16,
            ),
        ];
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Common\Carbon;
use Domain\Exchange\LtcsBillingStatementItemRecord;
use Domain\ServiceCode\ServiceCode;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\LtcsBillingStatementItemRecord} のテスト.
 */
final class LtcsBillingStatementItemRecordTest extends Test
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
                Csv::read(__DIR__ . '/LtcsBillingStatementItemRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\LtcsBillingStatementItemRecord[]
     */
    private function examples(): array
    {
        return [
            5 => new LtcsBillingStatementItemRecord(
                providedIn: Carbon::create(2019, 10),
                officeCode: '1371405083',
                insurerNumber: '131086',
                insNumber: '0001260082',
                serviceCode: ServiceCode::fromString('111312'),
                unitScore: 721,
                count: 13,
                totalScore: 9373,
                note: '',
                subsidies: [
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                ],
            ),
            12 => new LtcsBillingStatementItemRecord(
                providedIn: Carbon::create(2008, 05),
                officeCode: '1371405083',
                insurerNumber: '132019',
                insNumber: '1000535110',
                serviceCode: ServiceCode::fromString('116275'),
                unitScore: 478,
                count: 1,
                totalScore: 478,
                note: '',
                subsidies: [
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                ],
            ),
            75 => new LtcsBillingStatementItemRecord(
                providedIn: Carbon::create(2019, 1),
                officeCode: '1370406140',
                insurerNumber: '131045',
                insNumber: '0000846535',
                serviceCode: ServiceCode::fromString('117311'),
                unitScore: 223,
                count: 4,
                totalScore: 892,
                note: '',
                subsidies: [
                    new LtcsBillingStatementItemSubsidy(
                        count: 4,
                        totalScore: 892,
                    ),
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                    new LtcsBillingStatementItemSubsidy(
                        count: 0,
                        totalScore: 0,
                    ),
                ],
            ),
        ];
    }
}

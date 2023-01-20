<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Exchange\LtcsBillingStatementAggregateRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\LtcsBillingStatementAggregateRecord} のテスト.
 */
final class LtcsBillingStatementAggregateRecordTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_toArray(): void
    {
        $this->should('return valid csv values', function (): void {
            $this->assertEquals(
                Csv::read(__DIR__ . '/LtcsBillingStatementAggregateRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\LtcsBillingStatementAggregateRecord[]
     */
    private function examples(): array
    {
        return [
            7 => new LtcsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2019, 10),
                officeCode: '1371405083',
                insurerNumber: '131086',
                insNumber: '0001260082',
                serviceDivisionCode: '11',
                serviceDays: 19,
                plannedScore: 17675,
                managedScore: 17675,
                unmanagedScore: 2421,
                insurance: new LtcsBillingStatementAggregateInsurance(
                    totalScore: 20096,
                    unitCost: Decimal::fromInt(11_4000),
                    claimAmount: 160365,
                    copayAmount: 68729,
                ),
                subsidies: [
                    LtcsBillingStatementAggregateSubsidy::empty(),
                    LtcsBillingStatementAggregateSubsidy::empty(),
                    LtcsBillingStatementAggregateSubsidy::empty(),
                ],
            ),
            13 => new LtcsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2020, 12),
                officeCode: '1371405083',
                insurerNumber: '131177',
                insNumber: '0000183814',
                serviceDivisionCode: '11',
                serviceDays: 4,
                plannedScore: 3487,
                managedScore: 3487,
                unmanagedScore: 478,
                insurance: new LtcsBillingStatementAggregateInsurance(
                    totalScore: 3965,
                    unitCost: Decimal::fromInt(11_4000),
                    claimAmount: 36160,
                    copayAmount: 9041,
                ),
                subsidies: [
                    LtcsBillingStatementAggregateSubsidy::empty(),
                    LtcsBillingStatementAggregateSubsidy::empty(),
                    LtcsBillingStatementAggregateSubsidy::empty(),
                ],
            ),
            18 => new LtcsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2008, 05),
                officeCode: '1234567890',
                insurerNumber: '132019',
                insNumber: '1000535110',
                serviceDivisionCode: '11',
                serviceDays: 12,
                plannedScore: 6915,
                managedScore: 7890,
                unmanagedScore: 947,
                insurance: new LtcsBillingStatementAggregateInsurance(
                    totalScore: 8837,
                    unitCost: Decimal::fromInt(10_5000),
                    claimAmount: 64951,
                    copayAmount: 27837,
                ),
                subsidies: [
                    new LtcsBillingStatementAggregateSubsidy(
                        totalScore: 1000,
                        claimAmount: 8000,
                        copayAmount: 2000,
                    ),
                    new LtcsBillingStatementAggregateSubsidy(
                        totalScore: 2000,
                        claimAmount: 14000,
                        copayAmount: 6000,
                    ),
                    new LtcsBillingStatementAggregateSubsidy(
                        totalScore: 3000,
                        claimAmount: 27000,
                        copayAmount: 3000,
                    ),
                ],
            ),
        ];
    }
}

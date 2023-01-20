<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\LtcsBillingStatementInsurance;
use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\Common\Sex;
use Domain\Exchange\LtcsBillingStatementSummaryRecord;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsLevel;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\LtcsBillingStatementSummaryRecord} のテスト.
 */
final class LtcsBillingStatementSummaryRecordTest extends Test
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
                Csv::read(__DIR__ . '/LtcsBillingStatementSummaryRecordTest.csv')->toArray(),
                Arrays::generate(function (): iterable {
                    foreach ($this->generateExampleAttrs() as $recordNumber => $record) {
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
    private function generateExampleAttrs(): array
    {
        return [
            32 => new LtcsBillingStatementSummaryRecord(
                providedIn: Carbon::create(2019, 1),
                officeCode: '1370406140',
                insurerNumber: '131045',
                insNumber: '0000407726',
                birthday: Carbon::create(1933, 6, 10),
                sex: Sex::male(),
                level: LtcsLevel::careLevel5(),
                activatedOn: Carbon::create(2018, 8, 1),
                deactivatedOn: Carbon::create(2021, 7, 31),
                carePlanAuthorType: LtcsCarePlanAuthorType::careManagerOffice(),
                carePlanAuthorCode: '1370405381',
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 70,
                    totalScore: 17464,
                    claimAmount: 139362,
                    copayAmount: 59727,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
            ),
            113 => new LtcsBillingStatementSummaryRecord(
                providedIn: Carbon::create(2020, 11),
                officeCode: '1370406140',
                insurerNumber: '131045',
                insNumber: 'H201500761',
                birthday: Carbon::create(1972, 2, 19),
                sex: Sex::male(),
                level: LtcsLevel::careLevel4(),
                activatedOn: Carbon::create(2018, 2, 1),
                deactivatedOn: Carbon::create(2019, 1, 31),
                carePlanAuthorType: LtcsCarePlanAuthorType::careManagerOffice(),
                carePlanAuthorCode: '1371405174',
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 0,
                    totalScore: 4512,
                    claimAmount: 10403,
                    copayAmount: 0,
                ),
                subsidies: [
                    new LtcsBillingStatementSubsidy(
                        defrayerCategory: DefrayerCategory::atomicBombVictim(),
                        defrayerNumber: '12132015',
                        recipientNumber: '5007612',
                        benefitRate: 100,
                        totalScore: 4512,
                        claimAmount: 51436,
                        copayAmount: 0,
                    ),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
            ),
            74 => new LtcsBillingStatementSummaryRecord(
                providedIn: Carbon::create(2008, 5),
                officeCode: '1370406140',
                insurerNumber: '131045',
                insNumber: '0000846535',
                birthday: Carbon::create(1931, 5, 6),
                sex: Sex::female(),
                level: LtcsLevel::careLevel2(),
                activatedOn: Carbon::create(2018, 6, 1),
                deactivatedOn: Carbon::create(2019, 5, 31),
                carePlanAuthorType: LtcsCarePlanAuthorType::careManagerOffice(),
                carePlanAuthorCode: '1370403006',
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 1014,
                    claimAmount: 10403,
                    copayAmount: 0,
                ),
                subsidies: [
                    new LtcsBillingStatementSubsidy(
                        defrayerCategory: DefrayerCategory::livelihoodProtection(),
                        defrayerNumber: '25132010',
                        recipientNumber: '0001412',
                        benefitRate: 100,
                        totalScore: 1014,
                        claimAmount: 1156,
                        copayAmount: 0,
                    ),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
            ),
        ];
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\CopayCoordinationResult;
use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingStatementSummaryRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingStatementSummaryRecord} のテスト.
 */
final class DwsBillingStatementSummaryRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingStatementSummaryRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingStatementSummaryRecord[]
     */
    private function examples(): array
    {
        return [
            146 => new DwsBillingStatementSummaryRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131041',
                officeCode: '1311401366',
                dwsNumber: '0000060715',
                subsidyCityCode: '',
                userPhoneticName: 'ｸｳｼﾞｮｳｼﾞｮｳﾀﾛｳ',
                childPhoneticName: 'ｸｳｼﾞｮｳｼﾞｮﾘｰﾝ',
                dwsAreaGradeCode: '01',
                copayLimit: 37200,
                copayCoordinationOfficeCode: '1310401649',
                copayCoordinationResult: CopayCoordinationResult::appropriated(),
                coordinatedCopayAmount: 0,
                totalScore: 24932,
                totalFee: 279238,
                totalCappedCopay: 27923,
                totalAdjustedCopay: null,
                totalCoordinatedCopay: 0,
                totalCopay: 0,
                totalBenefit: 279238,
                totalSubsidy: 100,
            ),
            353 => new DwsBillingStatementSummaryRecord(
                providedIn: Carbon::create(2019, 2),
                cityCode: '131105',
                officeCode: '1311401366',
                dwsNumber: '3000007819',
                subsidyCityCode: '',
                userPhoneticName: 'ｶｷｮｳｲﾝﾉﾘｱｷ',
                childPhoneticName: 'ｴﾙﾒｪｽｺﾛｽﾃ',
                dwsAreaGradeCode: '02',
                copayLimit: 0,
                copayCoordinationOfficeCode: '',
                copayCoordinationResult: null,
                coordinatedCopayAmount: null,
                totalScore: 22499,
                totalFee: 251988,
                totalCappedCopay: 0,
                totalAdjustedCopay: null,
                totalCoordinatedCopay: null,
                totalCopay: 0,
                totalBenefit: 251988,
                totalSubsidy: null,
            ),
            413 => new DwsBillingStatementSummaryRecord(
                providedIn: Carbon::create(2008, 5),
                cityCode: '131113',
                officeCode: '1311401366',
                dwsNumber: '0700078728',
                subsidyCityCode: '',
                userPhoneticName: 'ﾋｶﾞｼｶﾞﾀｼﾞｮｳｽｹ',
                childPhoneticName: 'ｳｪｻﾞｰﾘﾎﾟｰﾄ',
                dwsAreaGradeCode: '03',
                copayLimit: 37200,
                copayCoordinationOfficeCode: '1311401366',
                copayCoordinationResult: CopayCoordinationResult::notCoordinated(),
                coordinatedCopayAmount: 11105,
                totalScore: 9916,
                totalFee: 111059,
                totalCappedCopay: 11105,
                totalAdjustedCopay: null,
                totalCoordinatedCopay: 11105,
                totalCopay: 11105,
                totalBenefit: 99954,
                totalSubsidy: null,
            ),
            590 => new DwsBillingStatementSummaryRecord(
                providedIn: Carbon::create(1999, 4),
                cityCode: '131113',
                officeCode: '1311401366',
                dwsNumber: '1000293868',
                subsidyCityCode: '',
                userPhoneticName: 'ﾆｼﾞﾑﾗｵｸﾔｽ',
                childPhoneticName: 'ﾅﾙｼｿｱﾅｽｲ',
                dwsAreaGradeCode: '04',
                copayLimit: 37200,
                copayCoordinationOfficeCode: '1311401366',
                copayCoordinationResult: CopayCoordinationResult::appropriated(),
                coordinatedCopayAmount: 37200,
                totalScore: 117703,
                totalFee: 1318273,
                totalCappedCopay: 37200,
                totalAdjustedCopay: null,
                totalCoordinatedCopay: 37200,
                totalCopay: 37200,
                totalBenefit: 1281073,
                totalSubsidy: null,
            ),
            707 => new DwsBillingStatementSummaryRecord(
                providedIn: Carbon::create(1995, 6),
                cityCode: '131148',
                officeCode: '1311401366',
                dwsNumber: '3000038244',
                subsidyCityCode: '',
                userPhoneticName: 'ｷｼﾍﾞﾛﾊﾝ',
                childPhoneticName: 'ｴﾝﾘｺﾌﾟｯﾁ',
                dwsAreaGradeCode: '04',
                copayLimit: 37200,
                copayCoordinationOfficeCode: '1311401861',
                copayCoordinationResult: CopayCoordinationResult::coordinated(),
                coordinatedCopayAmount: 4965,
                totalScore: 84903,
                totalFee: 950913,
                totalCappedCopay: 37200,
                totalAdjustedCopay: null,
                totalCoordinatedCopay: 4965,
                totalCopay: 4965,
                totalBenefit: 945948,
                totalSubsidy: null,
            ),
        ];
    }
}

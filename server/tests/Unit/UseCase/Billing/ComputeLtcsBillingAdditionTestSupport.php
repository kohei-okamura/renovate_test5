<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ProvisionReport\LtcsProvisionReportType;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\Timeframe;
use Lib\Csv;
use ScalikePHP\Seq;

/**
 * 介護保険サービス加算算定ユースケース実装のテスト関連ユーティリティ.
 */
trait ComputeLtcsBillingAdditionTestSupport
{
    /**
     * テスト用の介護保険サービス：予実を生成する.
     *
     * @param array $values
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    protected static function provisionReport(array $values = []): LtcsProvisionReport
    {
        $attrs = [
            'id' => 1,
            'userId' => 1,
            'officeId' => 1,
            'contractId' => 1,
            'providedIn' => Carbon::create(2022, 10, 01),
            'provisionReportType' => LtcsProvisionReportType::homeVisitLongTermCare(),
            'insurerNumber' => '131144', // 中野区
            'entries' => [self::provisionReportEntry()],
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'status' => LtcsProvisionReportStatus::fixed(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return LtcsProvisionReport::create($values + $attrs);
    }

    /**
     * テスト用の介護保険サービス：予実：サービス情報を生成する.
     *
     * @param array $values
     * @return \Domain\ProvisionReport\LtcsProvisionReportEntry
     */
    protected static function provisionReportEntry(array $values = []): LtcsProvisionReportEntry
    {
        $attrs = [
            'slot' => TimeRange::create(['start' => '10:00', 'end' => '12:00']),
            'timeframe' => Timeframe::daytime(),
            'category' => LtcsProjectServiceCategory::physicalCare(),
            'amounts' => [
                LtcsProjectAmount::create([
                    'category' => LtcsProjectAmountCategory::physicalCare(),
                    'amount' => 120,
                ]),
            ],
            'headcount' => 1,
            'ownExpenseProgramId' => null,
            'serviceCode' => ServiceCode::fromString('111411'),
            'options' => [],
            'note' => '',
            'plans' => [
                Carbon::create(2021, 1, 1),
                Carbon::create(2021, 1, 2),
            ],
            'results' => [
                Carbon::create(2021, 1, 1),
            ],
        ];
        return LtcsProvisionReportEntry::create($values + $attrs);
    }

    /**
     * テスト用の介護保険サービス：訪問介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry&\ScalikePHP\Seq
     */
    protected static function dictionaryEntries(): Seq
    {
        $id = 1;
        $csv = codecept_data_dir('ServiceCodeDictionary/ltcs-home-visit-long-term-care-dictionary-csv-example.csv');
        return Seq::from(...Csv::read($csv))
            ->map(function (array $row) use (&$id): LtcsHomeVisitLongTermCareDictionaryEntry {
                return LtcsHomeVisitLongTermCareDictionaryCsvRow::create($row)->toDictionaryEntry(['id' => $id++]);
            })
            ->computed();
    }
}

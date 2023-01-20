<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Office;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry;
use Domain\User\User;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf} のテスト.
 */
final class LtcsProvisionReportSheetAppendixPdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsInsCard $ltcsInsCard;
    private User $user;
    private Office $office;
    private LtcsProvisionReport $report;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProvisionReportSheetAppendixPdfTest $self): void {
            $self->ltcsInsCard = $self->examples->ltcsInsCards[0]->copy([
                'ltcsLevel' => LtcsLevel::careLevel3(),
                'copayRate' => 10,
            ]);
            $self->user = $self->examples->users[16];
            $self->office = $self->examples->offices[0]->copy([
                'ltcsHomeVisitLongTermCareService' => OfficeLtcsHomeVisitLongTermCareService::create([
                    'ltcsAreaGradeId' => $self->examples->ltcsAreaGrades[4]->id,
                    'code' => '1370406140',
                    'openedOn' => Carbon::now()->startOfDay(),
                    'designationExpiredOn' => Carbon::now()->endOfDay(),
                ]),
            ]);
            $self->report = $self->examples->ltcsProvisionReports[0]->copy(
                [
                    'plan' => new LtcsProvisionReportOverScore(
                        maxBenefitExcessScore: 0,
                        maxBenefitQuotaExcessScore: 0,
                    ),
                    'result' => new LtcsProvisionReportOverScore(
                        maxBenefitExcessScore: 0,
                        maxBenefitQuotaExcessScore: 0,
                    ),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function () {
            $actual = $this->createInstance();
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            $actual = LtcsProvisionReportSheetAppendixPdf::from(
                $this->examples->ltcsProvisionReportSheetAppendix[0]
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('return an instance with mask', function (): void {
            $actual = LtcsProvisionReportSheetAppendixPdf::from(
                $this->examples->ltcsProvisionReportSheetAppendix[0],
                true,
                true,
            );

            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createInstance()->toJson();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf
     */
    private function createInstance(array $attrs = []): LtcsProvisionReportSheetAppendixPdf
    {
        $values = $attrs + [
            'providedIn' => Carbon::parse('2022-01-01'),
            'insNumber' => '0123456789',
            'userName' => 'テスト名前',
            'entries' => [
                new LtcsProvisionReportSheetAppendixPdfEntry(
                    officeName: '土屋訪問介護事業所 新宿', // 事業所名
                    officeCode: '1370406140', // 事業所番号
                    serviceName: '身体介護1', // サービス内容/種類
                    serviceCode: '111111', // サービスコード
                    unitScore: number_format(250), // 単位数
                    count: '8', // 回数
                    wholeScore: number_format(2000), // サービス単位数/金額
                    maxBenefitQuotaExcessScore: number_format(0),
                    maxBenefitExcessScore: number_format(0),
                    scoreWithinMaxBenefitQuota: number_format(0),
                    scoreWithinMaxBenefit: number_format(0), // 区分支給限度基準内単位数
                    unitCost: sprintf('%.2f', 114000 / 100), // 単位数単価
                    totalFeeForInsuranceOrBusiness: number_format(0), // 費用総額(保険/事業対象分)
                    benefitRate: '0', // 給付率(%)
                    claimAmountForInsuranceOrBusiness: number_format(0), // 保険/事業費請求額
                    copayForInsuranceOrBusiness: number_format(0), // 利用者負担(保険/事業対象分)
                    copayWholeExpense: number_format(0), // 利用者負担(全額負担分)
                ),
            ],
            'maxBenefit' => number_format(36127),
            'totalScoreTotal' => number_format(10000),
            'maxBenefitExcessScoreTotal' => number_format(10000),
            'scoreWithinMaxBenefitTotal' => number_format(10000),
            'totalFeeForInsuranceOrBusinessTotal' => number_format(10000),
            'claimAmountForInsuranceOrBusinessTotal' => number_format(10000),
            'copayForInsuranceOrBusinessTotal' => number_format(10000),
            'copayWholeExpenseTotal' => number_format(10000),
            'insuranceClaimAmount' => number_format(10000),
            'subsidyClaimAmount' => number_format(10000),
            'copayAmount' => number_format(10000),
            'unitCost' => '11.10',
        ];
        return new LtcsProvisionReportSheetAppendixPdf(
            providedIn: $values['providedIn'],
            insNumber: $values['insNumber'],
            userName: $values['userName'],
            entries: $values['entries'],
            maxBenefit: $values['maxBenefit'],
            totalScoreTotal: $values['totalScoreTotal'],
            maxBenefitExcessScoreTotal: $values['maxBenefitExcessScoreTotal'],
            scoreWithinMaxBenefitTotal: $values['scoreWithinMaxBenefitTotal'],
            totalFeeForInsuranceOrBusinessTotal: $values['totalFeeForInsuranceOrBusinessTotal'],
            claimAmountForInsuranceOrBusinessTotal: $values['claimAmountForInsuranceOrBusinessTotal'],
            copayForInsuranceOrBusinessTotal: $values['copayForInsuranceOrBusinessTotal'],
            copayWholeExpenseTotal: $values['copayWholeExpenseTotal'],
            insuranceClaimAmount: $values['insuranceClaimAmount'],
            subsidyClaimAmount: $values['subsidyClaimAmount'],
            copayAmount: $values['copayAmount'],
            unitCost: $values['unitCost'],
        );
    }
}

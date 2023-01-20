<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportPdf;
use Domain\Billing\DwsBillingServiceReportPdfItem;
use Domain\Billing\DwsBillingServiceReportPdfPlan;
use Domain\Billing\DwsBillingServiceReportPdfResult;
use Domain\Billing\DwsBillingStatementContract;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use Tests\Unit\UseCase\Billing\DwsBillingTestSupport;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportPdf} のテスト.
 */
final class DwsBillingServiceReportPdfTest extends Test
{
    use CarbonMixin;
    use DwsBillingTestSupport;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createPdf();
            $this->assertMatchesJsonSnapshot($actual->toArray());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromBilling(): void
    {
        $this->should('build correct pdf model when the format is visitingCareForPwsd', function (): void {
            $report = $this->serviceReport->copy([
                'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
            ]);
            $expected = Seq::from($this->createExpectedPdf($report));
            $actual = $this->createPdf($report);
            $this->assertArrayStrictEquals($expected->toArray(), $actual->toArray());
        });

        $this->should('build correct pdf model when the format is homeHelpService', function (): void {
            $report = $this->serviceReport->copy([
                'format' => DwsBillingServiceReportFormat::homeHelpService(),
            ]);
            $expected = Seq::from($this->createExpectedPdf($report));
            $actual = $this->createPdf($report->copy([
                'items' => [
                    $report->items[1]->copy(['isPreviousMonth' => true]),
                    ...$report->items,
                    $report->items[0]->copy(['isPreviousMonth' => true]),
                ],
            ]));
            $this->assertArrayStrictEquals($expected->toArray(), $actual->toArray());
        });
    }

    /**
     * @param \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq $contracts 明細契約
     * @return \ScalikePHP\Seq&string[] 表示文字列（１行ごと）
     */
    private static function toGrantAmounts(Seq $contracts): Seq
    {
        return self::grantAmountForHomeHelpService($contracts)
            ->append(self::grantAmountForVisitingCareForPwsd($contracts));
    }

    /**
     * @param \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq $contracts
     * @return \ScalikePHP\Seq&string[]
     */
    private static function grantAmountForHomeHelpService(Seq $contracts): Seq
    {
        return $contracts->filter(function (DwsBillingStatementContract $x): bool {
            return $x->dwsGrantedServiceCode->toDwsServiceDivisionCode() === DwsServiceDivisionCode::homeHelpService();
        })->map(function (DwsBillingStatementContract $x): string {
            return DwsGrantedServiceCode::resolve($x->dwsGrantedServiceCode)
                . ' '
                . floor(($x->grantedAmount / 60) * 10) / 10 . '時間/月';
        });
    }

    /**
     * @param \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq $contracts
     * @return \ScalikePHP\Seq&string[]
     */
    private static function grantAmountForVisitingCareForPwsd(Seq $contracts): Seq
    {
        $movingDurationMinute = 0;
        $totalMinutes = $contracts
            ->filter(function (DwsBillingStatementContract $x): bool {
                return $x->dwsGrantedServiceCode->toDwsServiceDivisionCode() === DwsServiceDivisionCode::visitingCareForPwsd();
            })
            ->sumBy(function (int $z, DwsBillingStatementContract $x) use (&$movingDurationMinute): int {
                $sum = $z;
                if ($x->dwsGrantedServiceCode === DwsGrantedServiceCode::outingSupportForPwsd()) {
                    $movingDurationMinute = $x->grantedAmount;
                } else {
                    $sum += $x->grantedAmount;
                }
                return $sum;
            });
        if ($totalMinutes === 0) {
            return Seq::empty();
        }

        $movingHour = floor(($movingDurationMinute / 60) * 10) / 10;
        $moving = $movingDurationMinute === 0 ? ''
            : "(うち移動介護 {$movingHour}時間) ";
        $totalHour = floor(($totalMinutes / 60) * 10) / 10;
        $str = "重度訪問介護{$moving} {$totalHour}時間/月";
        return Seq::from($str);
    }

    /**
     * 検証用（期待値）の DwsBillingServiceReportPdf モデルを作成する
     *
     * @param null|\Domain\Billing\DwsBillingServiceReport $serviceReport
     * @return \Domain\Billing\DwsBillingServiceReportPdf
     */
    private function createExpectedPdf(?DwsBillingServiceReport $serviceReport = null): DwsBillingServiceReportPdf
    {
        $report = $serviceReport ?? $this->serviceReport;
        return new DwsBillingServiceReportPdf(
            providedIn: $this->bundle->providedIn,
            user: $report->user,
            office: $this->billing->office,
            items: DwsBillingServiceReportPdfItem::from($report->items),
            format: $report->format,
            plan: DwsBillingServiceReportPdfPlan::from($report->plan),
            result: DwsBillingServiceReportPdfResult::from($report->result),
            emergencyCount: $report->emergencyCount > 0 ? (string)$report->emergencyCount : '',
            firstTimeCount: $report->firstTimeCount > 0 ? (string)$report->firstTimeCount : '',
            welfareSpecialistCooperationCount: $report->welfareSpecialistCooperationCount > 0
                ? (string)$report->welfareSpecialistCooperationCount
                : '',
            behavioralDisorderSupportCooperationCount: $report->behavioralDisorderSupportCooperationCount > 0
                ? (string)$report->behavioralDisorderSupportCooperationCount
                : '',
            movingCareSupportCount: $report->movingCareSupportCount > 0
                ? (string)$report->movingCareSupportCount
                : '',
            grantAmounts: self::toGrantAmounts(Seq::fromArray($this->statement->contracts)),
            maxPageCount: ' ',
            currentPageCount: ' ',
        );
    }

    /**
     * DwsBillingServiceReportPdf モデルを作成する
     *
     * @param null|\Domain\Billing\DwsBillingServiceReport $serviceReport
     * @return \Domain\Billing\DwsBillingServiceReportPdf[]&\ScalikePHP\Seq
     */
    private function createPdf(?DwsBillingServiceReport $serviceReport = null): Seq
    {
        $report = $serviceReport ?? $this->serviceReport;
        return DwsBillingServiceReportPdf::from(
            $report,
            $this->bundle->providedIn,
            $this->billing->office,
            Seq::fromArray($this->statement->contracts)
        );
    }
}

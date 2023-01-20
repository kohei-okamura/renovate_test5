<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use Domain\Polite;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 障害：サービス提供実績記録票 PDF
 */
final class DwsBillingServiceReportPdf extends Polite
{
    /** @var int 重度訪問介護 サービス提供実績記録票の1枚あたりの明細数 */
    protected const VISITING_CARE_FOR_PWSD_ITEMS_PER_PAGE = 35;

    /** @var int 居宅介護 サービス提供実績記録票の1枚あたりの明細数 */
    protected const HOME_HELP_SERVICE_ITEMS_PER_PAGE = 28;

    /**
     * {@link \Domain\Billing\CopayListPdf} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param \Domain\Billing\DwsBillingUser $user 利用者
     * @param \Domain\Billing\DwsBillingOffice $office 事業所
     * @param \Domain\Billing\DwsBillingServiceReportItem[]&\ScalikePHP\Seq $items 明細
     * @param \Domain\Billing\DwsBillingServiceReportFormat $format 様式種別番号
     * @param \Domain\Billing\DwsBillingServiceReportPdfPlan $plan 合計（計画時間数）
     * @param \Domain\Billing\DwsBillingServiceReportPdfResult $result 合計（算定時間数）
     * @param string $emergencyCount 提供実績の合計：緊急時対応加算（回）
     * @param string $firstTimeCount 提供実績の合計：初回加算（回）
     * @param string $welfareSpecialistCooperationCount 提供実績の合計：福祉専門職員等連携加算（回）
     * @param string $behavioralDisorderSupportCooperationCount 提供実績の合計：行動障害支援連携加算（回）
     * @param string $movingCareSupportCount 提供実績の合計：移動介護緊急時支援加算
     * @param \ScalikePHP\Seq&string[] $grantAmounts 契約支給量
     * @param string $maxPageCount 現在ページ数
     * @param string $currentPageCount 最大ページ数
     */
    public function __construct(
        public readonly Carbon $providedIn,
        public readonly DwsBillingUser $user,
        public readonly DwsBillingOffice $office,
        public readonly Seq $items,
        public readonly DwsBillingServiceReportFormat $format,
        public readonly DwsBillingServiceReportPdfPlan $plan,
        public readonly DwsBillingServiceReportPdfResult $result,
        public readonly string $emergencyCount,
        public readonly string $firstTimeCount,
        public readonly string $welfareSpecialistCooperationCount,
        public readonly string $behavioralDisorderSupportCooperationCount,
        public readonly string $movingCareSupportCount,
        public readonly Seq $grantAmounts,
        public readonly string $maxPageCount,
        public readonly string $currentPageCount,
    ) {
    }

    /**
     * 空白となる行数を算出する.
     *
     * @return int
     */
    public function extraItemRows(): int
    {
        return $this->format->equals(DwsBillingServiceReportFormat::homeHelpService())
            ? self::HOME_HELP_SERVICE_ITEMS_PER_PAGE - count($this->items)
            : self::VISITING_CARE_FOR_PWSD_ITEMS_PER_PAGE - count($this->items);
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\DwsBillingServiceReport $serviceReport
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBillingOffice $office
     * @param \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq $contracts
     * @return \ScalikePHP\Seq&static[]
     */
    public static function from(
        DwsBillingServiceReport $serviceReport,
        Carbon $providedIn,
        DwsBillingOffice $office,
        Seq $contracts
    ): Seq {
        return Seq::from(...self::generate(
            $serviceReport,
            $providedIn,
            $office,
            $contracts
        ));
    }

    /**
     * 総ページ数を算出する.
     *
     * @param \Domain\Billing\DwsBillingServiceReport $report
     * @return string
     */
    private static function maxPageCount(DwsBillingServiceReport $report): string
    {
        $page = DwsBillingServiceReportFormat::visitingCareForPwsd() === $report->format
            ? Math::ceil(count($report->items) / self::VISITING_CARE_FOR_PWSD_ITEMS_PER_PAGE)
            : Math::ceil(count($report->items) / self::HOME_HELP_SERVICE_ITEMS_PER_PAGE);
        return $page > 1 ? (string)$page : ' ';
    }

    /**
     * インスタンスの一覧を生成する.
     *
     * @param \Domain\Billing\DwsBillingServiceReport $report
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBillingOffice $office
     * @param \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq $contracts
     * @return \Domain\Billing\DwsBillingServiceReportItem[]&iterable
     */
    private static function generate(
        DwsBillingServiceReport $report,
        Carbon $providedIn,
        DwsBillingOffice $office,
        Seq $contracts
    ): iterable {
        // 居宅の場合は月跨ぎにより前月分の明細が含まれている可能性があるためここで除外する（PDFには印字しない）
        [$items, $itemsPerPage] = $report->format === DwsBillingServiceReportFormat::homeHelpService()
            ? [
                Seq::from(...$report->items)->filter(fn (DwsBillingServiceReportItem $x): bool => !$x->isPreviousMonth),
                self::HOME_HELP_SERVICE_ITEMS_PER_PAGE,
            ]
            : [Seq::from(...$report->items), self::VISITING_CARE_FOR_PWSD_ITEMS_PER_PAGE];
        $xs = self::splitByPage($items, $itemsPerPage);
        foreach ($xs as $pageCount => $items) {
            yield new self(
                providedIn: $providedIn,
                user: $report->user,
                office: $office,
                items: DwsBillingServiceReportPdfItem::from($items),
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
                grantAmounts: self::toGrantAmounts($contracts),
                maxPageCount: self::maxPageCount($report),
                currentPageCount: self::maxPageCount($report) > 1 ? (string)$pageCount : ' ',
            );
        }
    }

    /**
     * 明細をページ単位に分割する.
     *
     * @param \Domain\Billing\DwsBillingServiceReportItem[]&\ScalikePHP\Seq $items
     * @param int $itemsPerPage
     * @param int $pageCount
     * @return \Domain\Billing\DwsBillingServiceReportItem[]&iterable
     */
    private static function splitByPage(Seq $items, int $itemsPerPage, int $pageCount = 1): iterable
    {
        $chunk = $items->take($itemsPerPage);
        $remain = $items->drop($itemsPerPage);

        yield $pageCount => $chunk;
        yield from $remain->isEmpty() ? [] : self::splitByPage($remain, $itemsPerPage, $pageCount + 1);
    }

    /**
     * @param \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq $contracts 明細契約
     * @return \ScalikePHP\Seq&string[] 表示文字列（1行ごと）
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
        return $contracts
            ->filter(function (DwsBillingStatementContract $x): bool {
                return $x->dwsGrantedServiceCode->toDwsServiceDivisionCode() === DwsServiceDivisionCode::homeHelpService();
            })
            ->map(function (DwsBillingStatementContract $x): string {
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
        $moving = $movingDurationMinute === 0
            ? ''
            : "(うち移動介護 {$movingHour}時間) ";
        $totalHour = floor(($totalMinutes / 60) * 10) / 10;
        $str = "重度訪問介護{$moving} {$totalHour}時間/月";
        return Seq::from($str);
    }
}

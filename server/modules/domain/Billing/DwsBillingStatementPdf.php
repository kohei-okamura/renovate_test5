<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;
use Domain\Pdf\PdfSupport;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書 PDF
 *
 * @property-read \Domain\Billing\DwsBillingOffice $office 事業所
 * @property-read string $cityCode 市町村番号
 * @property-read array $providedIn 提供年月日
 * @property-read null|string $subsidyCityCode 助成自治体番号
 * @property-read \Domain\Billing\DwsBillingUser $user 利用者
 * @property-read string $dwsAreaGradeName 地域区分名
 * @property-read int $copayLimit 利用者負担上限月額
 * @property-read array $copayCoordination 上限管理結果
 * @property-read array $daysRecords [[サービス種別, 利用開始日, 利用終了日, 利用日数]]
 * @property-read array $items 明細 [[サービスコード, 単位数, 回数, サービス単位数]]
 * @property-read array $aggregates 集計 [[サービス種別コード, サービス種別名, 利用日数, 給付単位数, 単位数単価, 総費用額, 1割相当額, 利用者負担額, 上限月額調整, 調整後利用者負担額, 上限額管理後利用者負担額, 決定利用者負担額, 請求額：給付費, 自治体助成分請求額]]
 * @property-read string $totalScore 請求額集計欄：合計：給付単位数
 * @property-read string $totalFee 請求額集計欄：合計：総費用額
 * @property-read string $totalCappedCopay 請求額集計欄：合計：上限月額調整
 * @property-read string $totalAdjustedCopay 請求額集計欄：合計：調整後利用者負担額
 * @property-read string $totalCoordinatedCopay 請求額集計欄：合計：上限管理後利用者負担額
 * @property-read string $totalCopay 請求額集計欄：合計：決定利用者負担額
 * @property-read string $totalBenefit 請求額集計欄：合計：請求額：給付費
 * @property-read string $totalSubsidy 請求額集計欄：合計：自治体助成分請求額
 * @property-read string $exemptionMeasure 就労継続支援A型事業者負担減免措置実施
 * @property-read string $exemptionTarget 就労継続支援A型減免対象者
 */
class DwsBillingStatementPdf extends Model
{
    use PdfSupport;

    private const ITEM_ROWS = 13;
    private const DAYS_RECORD_ROWS = 3;
    private const AGGREGATE_COLUMNS = 4;
    private const EXEMPTION_MEASURE = 1; // 居宅・重訪においては不要のため固定値
    private const EXEMPTION_TARGET = 1; // 居宅・重訪においては不要のため固定値
    private ?int $pages = null;

    /**
     * ページ数を返す.
     *
     * @return int
     */
    public function pages(): int
    {
        if ($this->pages === null) {
            $this->pages = $this->computePage();
        }
        return $this->pages;
    }

    /**
     * ページ数をPDFに描画する形式に変換する.
     *
     * @param int $page
     * @return string
     */
    public static function formatedPage(int $page): string
    {
        return sprintf('%02d', $page);
    }

    /**
     * 最終ページか判定する.
     *
     * @param int $page
     * @return bool
     */
    public function isLastPage(int $page): bool
    {
        if ($this->pages === null) {
            $this->pages = $this->computePage();
        }
        return $page === $this->pages;
    }

    /**
     * サービス種別：空白となる行数を算出する.
     *
     * @return int
     */
    public function extraDaysRecordRows(): int
    {
        return self::DAYS_RECORD_ROWS - count($this->daysRecords);
    }

    /**
     * 当該ページに記載する明細項目を返す.
     *
     * @param int $page
     * @return array
     */
    public function itemsInThePage(int $page): array
    {
        return array_slice($this->items, self::ITEM_ROWS * ($page - 1), self::ITEM_ROWS);
    }

    /**
     * 明細欄：必要な行数を算出する.
     *
     * @return int
     */
    public function itemRows(): int
    {
        return self::ITEM_ROWS;
    }

    /**
     * 明細欄：空白となる行数を算出する.
     *
     * @param int $page
     * @return int
     */
    public function extraItemRows(int $page): int
    {
        $items = $this->itemsInThePage($page);
        return self::ITEM_ROWS > count($items)
            ? self::ITEM_ROWS - count($items)
            : 0;
    }

    /**
     * 集計欄：空白となる行数を算出する.
     *
     * @param int $page
     * @return int
     */
    public function extraAggregateColumns(int $page): int
    {
        return $this->isLastPage($page)
            ? self::AGGREGATE_COLUMNS - count($this->aggregates)
            : self::AGGREGATE_COLUMNS;
    }

    /**
     * 障害福祉サービス：請求書 PDF ドメインモデルを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param \ScalikePHP\Map $serviceCodeMap [サービスコード => 辞書エントリ]
     * @return static
     */
    public static function from(
        DwsBilling $billing,
        DwsBillingBundle $bundle,
        DwsBillingStatement $statement,
        Map $serviceCodeMap
    ): self {
        return self::create([
            'office' => $billing->office,
            'cityCode' => $bundle->cityCode,
            'providedIn' => self::localized($bundle->providedIn),
            'subsidyCityCode' => self::subsidyCityCode($statement),
            'user' => $statement->user,
            'dwsAreaGradeName' => $statement->dwsAreaGradeName,
            'copayLimit' => self::convertToFixedLengthString($statement->copayLimit, 5),
            'copayCoordination' => self::copayCoordination($statement->copayCoordination),
            'daysRecords' => self::daysRecords($statement),
            'items' => self::items($statement->items, $serviceCodeMap),
            'aggregates' => self::aggregates($statement->aggregates),
            'totalScore' => self::convertToFixedLengthString($statement->totalScore),
            'totalFee' => self::convertToFixedLengthString($statement->totalFee),
            'totalCappedCopay' => self::convertToFixedLengthString($statement->totalCappedCopay),
            'totalAdjustedCopay' => self::convertToFixedLengthString($statement->totalAdjustedCopay),
            'totalCoordinatedCopay' => self::convertToFixedLengthString($statement->totalCoordinatedCopay),
            'totalCopay' => self::convertToFixedLengthString($statement->totalCopay),
            'totalBenefit' => self::convertToFixedLengthString($statement->totalBenefit),
            'totalSubsidy' => self::convertToFixedLengthString($statement->totalSubsidy),
            'exemptionMeasure' => self::EXEMPTION_MEASURE,
            'exemptionTarget' => self::EXEMPTION_TARGET,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'office',
            'cityCode',
            'providedIn',
            'subsidyCityCode',
            'user',
            'dwsAreaGradeName',
            'copayLimit',
            'copayCoordination',
            'daysRecords',
            'items',
            'aggregates',
            'totalScore',
            'totalFee',
            'totalCappedCopay',
            'totalAdjustedCopay',
            'totalCoordinatedCopay',
            'totalCopay',
            'totalBenefit',
            'totalSubsidy',
            'exemptionMeasure',
            'exemptionTarget',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'office' => true,
            'cityCode' => true,
            'providedIn' => true,
            'subsidyCityCode' => true,
            'user' => true,
            'dwsAreaGradeName' => true,
            'copayLimit' => true,
            'copayCoordination' => true,
            'daysRecords' => true,
            'items' => true,
            'aggregates' => true,
            'totalScore' => true,
            'totalFee' => true,
            'totalCappedCopay' => true,
            'totalAdjustedCopay' => true,
            'totalCoordinatedCopay' => true,
            'totalCopay' => true,
            'totalBenefit' => true,
            'totalSubsidy' => true,
            'exemptionMeasure' => true,
            'exemptionTarget' => true,
        ];
    }

    /**
     * ページ数を算出する.
     *
     * @return int
     */
    private function computePage(): int
    {
        return $this->pages = (int)ceil(count($this->items) / self::ITEM_ROWS);
    }

    /**
     * 障害福祉サービス明細書：上限管理結果をPDFに描画する形式に変換する.
     *
     * @param null|\Domain\Billing\DwsBillingStatementCopayCoordination $copayCoordination
     * @return array
     */
    private static function copayCoordination(?DwsBillingStatementCopayCoordination $copayCoordination): array
    {
        if ($copayCoordination === null) {
            return [
                'code' => str_repeat(' ', 10),
                'name' => '',
                'result' => ' ',
                'amount' => str_repeat(' ', 5),
            ];
        }

        return [
            'code' => $copayCoordination->office->code,
            'name' => $copayCoordination->office->name,
            'result' => $copayCoordination->result->value(),
            'amount' => self::convertToFixedLengthString($copayCoordination->amount, 5),
        ];
    }

    /**
     * 助成自治体番号をPDFに描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return string
     */
    private static function subsidyCityCode(DwsBillingStatement $statement): string
    {
        return $statement->subsidyCityCode !== null
            ? sprintf('% 6s', $statement->subsidyCityCode)
            : str_repeat(' ', 6);
    }

    /**
     * 集計をPDFに描画する形式に変換する.
     *
     * @param array $aggregates
     * @return array
     */
    private static function aggregates(array $aggregates): array
    {
        return Seq::fromArray($aggregates)
            ->map(
                fn (DwsBillingStatementAggregate $x): DwsBillingStatementPdfAggregate => DwsBillingStatementPdfAggregate::from($x)
            )
            ->toArray();
    }

    /**
     * 明細をPDFに描画する形式に変換する.
     *
     * @param array $items
     * @param \ScalikePHP\Map $serviceCodeMap [[サービスコード => 辞書エントリ]]
     * @return array
     */
    private static function items(array $items, Map $serviceCodeMap): array
    {
        return Seq::fromArray($items)
            ->map(
                fn (DwsBillingStatementItem $x): DwsBillingStatementPdfItem => DwsBillingStatementPdfItem::from($x, $serviceCodeMap)
            )
            ->toArray();
    }

    /**
     * サービス種別、利用開始日/終了日、利用日数をPDFに描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return array
     */
    private static function daysRecords(DwsBillingStatement $statement): array
    {
        return Seq::fromArray($statement->aggregates)
            ->map(function (DwsBillingStatementAggregate $x): array {
                return [
                    'dwsServiceDivisionCode' => $x->serviceDivisionCode->value(),
                    'startedOn' => self::localized($x->startedOn),
                    'terminatedOn' => self::localized($x->terminatedOn),
                    'serviceDays' => self::convertToFixedLengthString($x->serviceDays, 2),
                ];
            })
            ->toArray();
    }

    /**
     * 数値を固定帳の文字列にして返す（足りない分は前に空白を追加する）
     * 数値が null の場合は指定桁数の空文字列を返す
     *
     * @param null|int|string $value
     * @param int $digits 桁数（7桁が多いのでデフォルトは 7）
     * @return string
     */
    private static function convertToFixedLengthString($value, int $digits = 7): string
    {
        return $value !== null ? sprintf("% {$digits}d", $value) : str_repeat(' ', $digits);
    }
}

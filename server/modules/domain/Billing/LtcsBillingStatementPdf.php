<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Pdf\PdfSupport;
use Domain\Polite;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書 PDF.
 */
final class LtcsBillingStatementPdf extends Polite
{
    use PdfSupport;

    private const ITEM_ROWS = 10;
    private const AGGREGATE_COLUMNS = 4;

    /**
     * {@link \Domain\Billing\LtcsBillingStatementPdf} constructor.
     *
     * @param \Domain\Billing\LtcsBillingOffice $office 介護保険：請求：事業所
     * @param string $defrayerNumber
     * @param string $recipientNumber
     * @param array $providedIn
     * @param string $insurerNumber
     * @param \Domain\Billing\LtcsBillingUser $user
     * @param array $userBirthday
     * @param array $userActivatedOn
     * @param array $userDeactivatedOn
     * @param \Domain\Billing\LtcsCarePlanAuthor $carePlanAuthor
     * @param array $agreedOn
     * @param array $expiredOn
     * @param int $expiredReason
     * @param array&\Domain\Billing\LtcsBillingStatementPdfItem[] $items
     * @param array&\Domain\Billing\LtcsBillingStatementPdfAggregate[] $aggregates
     * @param string $insuranceBenefitRate 保険給付率(%)
     * @param array|string[] $subsidyBenefitRate 公費給付率(%)
     * @param string $totalInsuranceClaimAmount
     * @param string $totalInsuranceCopayAmount
     * @param string $totalSubsidyClaimAmount
     * @param string $totalSubsidyCopayAmount
     */
    public function __construct(
        public readonly LtcsBillingOffice $office,
        public readonly string $defrayerNumber,
        public readonly string $recipientNumber,
        public readonly array $providedIn,
        public readonly string $insurerNumber,
        public readonly LtcsBillingUser $user,
        public readonly array $userBirthday,
        public readonly array $userActivatedOn,
        public readonly array $userDeactivatedOn,
        public readonly LtcsCarePlanAuthor $carePlanAuthor,
        public readonly array $agreedOn,
        public readonly array $expiredOn,
        public readonly int $expiredReason,
        public readonly array $items,
        public readonly array $aggregates,
        public readonly string $insuranceBenefitRate,
        public readonly array $subsidyBenefitRate,
        public readonly string $totalInsuranceClaimAmount,
        public readonly string $totalInsuranceCopayAmount,
        public readonly string $totalSubsidyClaimAmount,
        public readonly string $totalSubsidyCopayAmount
    ) {
    }

    /**
     * 明細書と請求単位から明細書PDFを生成する.
     *
     * @param \Domain\Billing\LtcsBillingOffice $office
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @param \ScalikePHP\Map $serviceCodeMap サービス名称Map [サービスコード => 名称, ...]
     * @return \Domain\Billing\LtcsBillingStatementPdf
     */
    public static function from(
        LtcsBillingOffice $office,
        LtcsBillingBundle $bundle,
        LtcsBillingStatement $statement,
        Map $serviceCodeMap
    ): self {
        return new self(
            office: $office,
            defrayerNumber: isset($statement->subsidies[0]) ? $statement->subsidies[0]->defrayerNumber : str_repeat(' ', 8),
            recipientNumber: isset($statement->subsidies[0]) ? $statement->subsidies[0]->recipientNumber : str_repeat(' ', 7),
            providedIn: self::localized($bundle->providedIn),
            insurerNumber: $statement->insurerNumber,
            user: $statement->user,
            userBirthday: self::localized($statement->user->birthday),
            userActivatedOn: self::localized($statement->user->activatedOn),
            userDeactivatedOn: self::localized($statement->user->deactivatedOn),
            carePlanAuthor: $statement->carePlanAuthor,
            agreedOn: self::localized($statement->agreedOn),
            expiredOn: self::localized($statement->expiredOn),
            expiredReason: $statement->expiredReason->value(),
            items: self::items($statement->items, $serviceCodeMap),
            aggregates: self::aggregates($statement->aggregates),
            insuranceBenefitRate: sprintf('% 3d', $statement->insurance->benefitRate),
            subsidyBenefitRate: preg_split('//u', sprintf('% 3d', $statement->subsidies[0]->benefitRate)),
            totalInsuranceClaimAmount: self::totalInsuranceClaimAmount($statement->aggregates),
            totalInsuranceCopayAmount: self::totalInsuranceCopayAmount($statement->aggregates),
            totalSubsidyClaimAmount: self::totalSubsidyClaimAmount($statement->aggregates),
            totalSubsidyCopayAmount: self::totalSubsidyCopayAmount($statement->aggregates),
        );
    }

    /**
     * 空白となる行数を算出する.
     *
     * @return int
     */
    public function extraItemRows(): int
    {
        return self::ITEM_ROWS - count($this->items);
    }

    /**
     * 空白となる列数を算出する.
     *
     * @return int
     */
    public function extraAggregateColumns(): int
    {
        return self::AGGREGATE_COLUMNS - count($this->aggregates);
    }

    /**
     * 保険利用者負担額合計.
     *
     * @param array|\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates
     * @return string
     */
    private static function totalInsuranceClaimAmount(array $aggregates): string
    {
        $sum = Seq::fromArray($aggregates)
            ->map(fn (LtcsBillingStatementAggregate $x): int => $x->insurance->claimAmount)
            ->sum();
        return sprintf('% 6d', $sum);
    }

    /**
     * 保険請求額合計.
     *
     * @param array|\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates
     * @return string
     */
    private static function totalInsuranceCopayAmount(array $aggregates): string
    {
        $sum = Seq::fromArray($aggregates)
            ->map(fn (LtcsBillingStatementAggregate $x): int => $x->insurance->copayAmount)
            ->sum();
        return sprintf('% 6d', $sum);
    }

    /**
     * 公費利用者負担額合計.
     *
     * @param array|\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates
     * @return string
     */
    private static function totalSubsidyCopayAmount(array $aggregates): string
    {
        $sum = Seq::fromArray($aggregates)
            ->map(function (LtcsBillingStatementAggregate $x) {
                if (!isset($x->subsidies[0])) {
                    return 0;
                }
                return $x->subsidies[0]->copayAmount;
            })
            ->sum();
        return sprintf('% 6d', $sum);
    }

    /**
     * 公費請求額合計.
     *
     * @param array|\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates
     * @return string
     */
    private static function totalSubsidyClaimAmount(array $aggregates): string
    {
        $sum = Seq::fromArray($aggregates)
            ->map(function (LtcsBillingStatementAggregate $x) {
                if (!isset($x->subsidies[0])) {
                    return 0;
                }
                return $x->subsidies[0]->claimAmount;
            })
            ->sum();
        return sprintf('% 6d', $sum);
    }

    /**
     * 明細をPDFに描画する形式に変換する.
     *
     * @param array|\Domain\Billing\LtcsBillingStatementItem[] $items
     * @param \ScalikePHP\Map $serviceCodeMap [[サービスコード => 辞書エントリ]]
     * @return array|array[] 明細行（連想配列）の配列
     */
    private static function items(array $items, Map $serviceCodeMap): array
    {
        return Seq::fromArray($items)
            ->map(
                fn (LtcsBillingStatementItem $item): LtcsBillingStatementPdfItem => LtcsBillingStatementPdfItem::from($item, $serviceCodeMap)
            )
            ->toArray();
    }

    /**
     * 集計をPDFに描画する形式に変換する.
     *
     * @param array|\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates
     * @return array
     */
    private static function aggregates(array $aggregates): array
    {
        return Seq::fromArray($aggregates)
            ->map(
                fn (LtcsBillingStatementAggregate $aggregate): LtcsBillingStatementPdfAggregate => LtcsBillingStatementPdfAggregate::from($aggregate)
            )
            ->toArray();
    }
}

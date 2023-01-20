<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Polite;
use Lib\Strings;

/**
 * 介護保険サービス：サービス提供票別表PDF.
 */
final class LtcsProvisionReportSheetAppendixPdf extends Polite
{
    private const ENTRY_RECORD_ROWS = 14;

    /**
     * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $insNumber 被保険者証番号
     * @param string $userName 利用者氏名
     * @param array|\Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry[] $entries サービス情報
     * @param string $maxBenefit 区分支給限度基準額（単位）
     * @param string $totalScoreTotal サービス単位数/金額（合計）
     * @param string $maxBenefitExcessScoreTotal 区分支給限度基準を超える単位数（合計）
     * @param string $scoreWithinMaxBenefitTotal 区分支給限度基準内単位数（合計）
     * @param string $totalFeeForInsuranceOrBusinessTotal 費用総額(保険/事業対象分)（合計）
     * @param string $claimAmountForInsuranceOrBusinessTotal 保険/事業費請求額（合計）
     * @param string $copayForInsuranceOrBusinessTotal 利用者負担(保険/事業対象分)（合計）
     * @param string $copayWholeExpenseTotal 利用者負担(全額負担分)（合計）
     * @param string $insuranceClaimAmount 保険請求分
     * @param string $subsidyClaimAmount 公費請求額
     * @param string $copayAmount 利用者請求額
     * @param string $unitCost 単位数単価
     */
    public function __construct(
        public readonly Carbon $providedIn,
        public readonly string $insNumber,
        public readonly string $userName,
        public readonly array $entries,
        public readonly string $maxBenefit,
        public readonly string $totalScoreTotal,
        public readonly string $maxBenefitExcessScoreTotal,
        public readonly string $scoreWithinMaxBenefitTotal,
        public readonly string $totalFeeForInsuranceOrBusinessTotal,
        public readonly string $claimAmountForInsuranceOrBusinessTotal,
        public readonly string $copayForInsuranceOrBusinessTotal,
        public readonly string $copayWholeExpenseTotal,
        public readonly string $insuranceClaimAmount,
        public readonly string $subsidyClaimAmount,
        public readonly string $copayAmount,
        public readonly string $unitCost
    ) {
    }

    /**
     * 介護保険サービス：サービス提供票別表PDF を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix $appendix
     * @param bool $needsMaskingInsNumber 被保険者番号マスキング要否
     * @param bool $needsMaskingInsName 被保険者氏名マスキング要否
     * @return $this
     */
    public static function from(
        LtcsProvisionReportSheetAppendix $appendix,
        bool $needsMaskingInsNumber = false,
        bool $needsMaskingInsName = false
    ): self {
        $insNumber = $needsMaskingInsNumber ? self::maskInsNumber($appendix->insNumber) : $appendix->insNumber;
        $userName = $needsMaskingInsName ? self::maskInsName($appendix->userName) : $appendix->userName;
        return new self(
            providedIn: $appendix->providedIn,
            insNumber: $insNumber,
            userName: $userName,
            entries: LtcsProvisionReportSheetAppendixPdfEntry::from($appendix->managedEntries, $appendix->unmanagedEntries, $appendix->managedTotalEntry)->toArray(),
            maxBenefit: number_format($appendix->maxBenefit),
            totalScoreTotal: number_format($appendix->totalScoreTotal),
            maxBenefitExcessScoreTotal: number_format($appendix->maxBenefitExcessScoreTotal),
            scoreWithinMaxBenefitTotal: number_format($appendix->scoreWithinMaxBenefitTotal),
            totalFeeForInsuranceOrBusinessTotal: number_format($appendix->totalFeeForInsuranceOrBusinessTotal),
            claimAmountForInsuranceOrBusinessTotal: number_format($appendix->claimAmountForInsuranceOrBusinessTotal),
            copayForInsuranceOrBusinessTotal: number_format($appendix->copayForInsuranceOrBusinessTotal),
            copayWholeExpenseTotal: number_format($appendix->copayWholeExpenseTotal),
            insuranceClaimAmount: number_format($appendix->insuranceClaimAmount),
            subsidyClaimAmount: number_format($appendix->subsidyClaimAmount),
            copayAmount: number_format($appendix->copayAmount),
            unitCost: sprintf('%.2f', $appendix->unitCost->toFloat()),
        );
    }

    /**
     * 区分支給限度管理・利用者負担計算の空白行数を算出する.
     *
     * @return int
     */
    public function extraEntryRows(): int
    {
        return self::ENTRY_RECORD_ROWS - count($this->entries);
    }

    /**
     * 被保険者番号の先頭6桁を伏せ字にする.
     *
     * @param string $insNumber
     * @return string
     */
    private static function maskInsNumber(string $insNumber): string
    {
        return Strings::mask($insNumber, '*', fn (string $char, int $index): bool => $index < 6);
    }

    /**
     * 被保険者氏名を伏せ字にする.
     *
     * @param string $insName
     * @return string
     */
    private static function maskInsName(string $insName): string
    {
        $spaceCount = 0;
        return Strings::mask($insName, '●', function (string $char, int $index) use (&$spaceCount): bool {
            if ($char === ' ') {
                ++$spaceCount;
                return false;
            }
            return ($index - $spaceCount) % 2 === 1;
        });
    }
}

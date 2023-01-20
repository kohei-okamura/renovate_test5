<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Carbon;

/**
 * 障害：介護給付費等明細書：基本情報レコード.
 */
final class DwsBillingStatementSummaryRecord extends DwsBillingStatementRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingStatementSummaryRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param string $subsidyCityCode 助成自治体番号
     * @param string $userPhoneticName 支給決定者氏名カナ
     * @param string $childPhoneticName 支給決定児童氏名カナ
     * @param string $dwsAreaGradeCode 地域区分コード
     * @param int $copayLimit 利用者負担上限月額
     * @param string $copayCoordinationOfficeCode 上限管理事業所：指定事業所番号
     * @param null|\Domain\Billing\CopayCoordinationResult $copayCoordinationResult 上限管理事業所：管理結果
     * @param null|int $coordinatedCopayAmount 上限管理事業所：管理結果額
     * @param int $totalScore 請求額集計欄：合計：給付単位数
     * @param int $totalFee 請求額集計欄：合計：総費用額
     * @param int $totalCappedCopay 請求額集計欄：合計：上限月額調整
     * @param null|int $totalAdjustedCopay 請求額集計欄：合計：調整後利用者不異端額
     * @param null|int $totalCoordinatedCopay 請求額集計欄：合計：上限管理後利用者負担額
     * @param int $totalCopay 請求額集計欄：合計：決定利用者負担額
     * @param int $totalBenefit 請求額集計欄：合計：請求額：給付費
     * @param null|int $totalSubsidy 請求額集計欄：合計：自治体助成分請求額
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        #[JsonIgnore] public readonly string $subsidyCityCode,
        #[JsonIgnore] public readonly string $userPhoneticName,
        #[JsonIgnore] public readonly string $childPhoneticName,
        #[JsonIgnore] public readonly string $dwsAreaGradeCode,
        #[JsonIgnore] public readonly int $copayLimit,
        #[JsonIgnore] public readonly string $copayCoordinationOfficeCode,
        #[JsonIgnore] public readonly ?CopayCoordinationResult $copayCoordinationResult,
        #[JsonIgnore] public readonly ?int $coordinatedCopayAmount,
        #[JsonIgnore] public readonly int $totalScore,
        #[JsonIgnore] public readonly int $totalFee,
        #[JsonIgnore] public readonly int $totalCappedCopay,
        #[JsonIgnore] public readonly ?int $totalAdjustedCopay,
        #[JsonIgnore] public readonly ?int $totalCoordinatedCopay,
        #[JsonIgnore] public readonly int $totalCopay,
        #[JsonIgnore] public readonly int $totalBenefit,
        #[JsonIgnore] public readonly ?int $totalSubsidy
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_SUMMARY,
            providedIn: $providedIn,
            cityCode: $cityCode,
            officeCode: $officeCode,
            dwsNumber: $dwsNumber
        );
    }

    /**
     * インスタンス生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return static
     */
    public static function from(DwsBilling $billing, DwsBillingBundle $bundle, DwsBillingStatement $statement): self
    {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            dwsNumber: $statement->user->dwsNumber,
            subsidyCityCode: $statement->subsidyCityCode ?? '',
            userPhoneticName: $statement->user->name->phoneticFamilyName . $statement->user->name->phoneticGivenName,
            childPhoneticName: $statement->user->childName->phoneticFamilyName . $statement->user->childName->phoneticGivenName,
            dwsAreaGradeCode: $statement->dwsAreaGradeCode,
            copayLimit: $statement->user->copayLimit,
            copayCoordinationOfficeCode: $statement->copayCoordination?->office->code ?? '',
            copayCoordinationResult: $statement->copayCoordination?->result,
            coordinatedCopayAmount: $statement->copayCoordination?->amount,
            totalScore: $statement->totalScore,
            totalFee: $statement->totalFee,
            totalCappedCopay: $statement->totalCappedCopay,
            totalAdjustedCopay: $statement->totalAdjustedCopay,
            totalCoordinatedCopay: $statement->totalCoordinatedCopay,
            totalCopay: $statement->totalCopay,
            totalBenefit: $statement->totalBenefit,
            totalSubsidy: $statement->totalSubsidy,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->subsidyCityCode,
            mb_convert_kana($this->userPhoneticName, 'k'),
            mb_convert_kana($this->childPhoneticName, 'k'),
            $this->dwsAreaGradeCode,
            1,
            $this->copayLimit,
            1,
            self::UNUSED,
            $this->copayCoordinationOfficeCode,
            $this->copayCoordinationResult?->value() ?? '',
            $this->coordinatedCopayAmount ?? '',
            self::UNUSED,
            self::UNUSED,
            $this->totalScore,
            $this->totalFee,
            $this->totalCappedCopay,
            self::UNUSED,
            self::UNUSED,
            $this->totalAdjustedCopay ?? '',
            $this->totalCoordinatedCopay ?? '',
            $this->totalCopay,
            $this->totalBenefit,
            self::UNUSED,
            self::UNUSED,
            $this->totalSubsidy ?? '',
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
        ];
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Common\Carbon;

/**
 * 障害：利用者負担上限額管理結果票：基本情報レコード.
 */
final class DwsBillingCopayCoordinationSummaryRecord extends DwsBillingCopayCoordinationRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param \Domain\Billing\DwsBillingCopayCoordinationExchangeAim $copayCoordinationExchangeAim 上限額管理結果票情報作成区分コード
     * @param string $cityCode 市町村番号
     * @param string $copayCoordinationOfficeCode 上限額管理事業所番号
     * @param string $dwsNumber 受給者証番号
     * @param string $userPhoneticDisplayName 支給決定者氏名カナ
     * @param string $childPhoneticDisplayName 支給決定児童氏名カナ
     * @param int $copayLimit 利用者負担上限月額
     * @param \Domain\Billing\CopayCoordinationResult $result 利用者負担上限額管理結果
     * @param int $totalFee 請求額集計欄：合計：総費用額
     * @param int $totalCopay 合計：利用者負担額
     * @param int $coordinatedCopay 管理結果後利用者負担額
     */
    public function __construct(
        Carbon $providedIn,
        #[JsonIgnore] public readonly DwsBillingCopayCoordinationExchangeAim $copayCoordinationExchangeAim,
        #[JsonIgnore] public readonly string $cityCode,
        #[JsonIgnore] public readonly string $copayCoordinationOfficeCode,
        #[JsonIgnore] public readonly string $dwsNumber,
        #[JsonIgnore] public readonly string $userPhoneticDisplayName,
        #[JsonIgnore] public readonly string $childPhoneticDisplayName,
        #[JsonIgnore] public readonly int $copayLimit,
        #[JsonIgnore] public readonly CopayCoordinationResult $result,
        #[JsonIgnore] public readonly int $totalFee,
        #[JsonIgnore] public readonly int $totalCopay,
        #[JsonIgnore] public readonly int $coordinatedCopay
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_SUMMARY,
            providedIn: $providedIn
        );
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingCopayCoordination $copayCoordination
     * @return \Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord
     */
    public static function from(DwsBillingBundle $bundle, DwsBillingCopayCoordination $copayCoordination): self
    {
        return new self(
            providedIn: $bundle->providedIn,
            copayCoordinationExchangeAim: $copayCoordination->exchangeAim,
            cityCode: $bundle->cityCode,
            copayCoordinationOfficeCode: $copayCoordination->office->code,
            dwsNumber: $copayCoordination->user->dwsNumber,
            userPhoneticDisplayName: $copayCoordination->user->name->phoneticFamilyName . $copayCoordination->user->name->phoneticGivenName,
            childPhoneticDisplayName: $copayCoordination->user->childName->phoneticFamilyName . $copayCoordination->user->childName->phoneticGivenName,
            copayLimit: $copayCoordination->user->copayLimit,
            result: $copayCoordination->result,
            totalFee: $copayCoordination->total->fee,
            totalCopay: $copayCoordination->total->copay,
            coordinatedCopay: $copayCoordination->total->coordinatedCopay,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->copayCoordinationExchangeAim->value(),
            $this->cityCode,
            $this->copayCoordinationOfficeCode,
            $this->dwsNumber,
            mb_convert_kana($this->userPhoneticDisplayName, 'k'),
            mb_convert_kana($this->childPhoneticDisplayName, 'k'),
            $this->copayLimit,
            $this->result->value(),
            $this->totalFee,
            $this->totalCopay,
            $this->coordinatedCopay,
        ];
    }
}

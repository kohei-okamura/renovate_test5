<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatementContract;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Lib\Math;

/**
 * 障害：介護給付費等明細書：契約情報レコード.
 */
final class DwsBillingStatementContractRecord extends DwsBillingStatementRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingStatementContractRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param \Domain\Billing\DwsGrantedServiceCode $dwsGrantedServiceCode 決定サービスコード
     * @param int $grantedAmount 契約支給量（分単位）
     * @param \Domain\Common\Carbon $agreedOn 契約開始年月日
     * @param null|\Domain\Common\Carbon $expiredOn 契約終了年月日
     * @param int $indexNumber 事業者記入欄番号
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        #[JsonIgnore] public readonly DwsGrantedServiceCode $dwsGrantedServiceCode,
        #[JsonIgnore] public readonly int $grantedAmount,
        #[JsonIgnore] public readonly Carbon $agreedOn,
        #[JsonIgnore] public readonly ?Carbon $expiredOn,
        #[JsonIgnore] public readonly int $indexNumber
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_CONTRACT,
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
     * @param \Domain\Billing\DwsBillingUser $user
     * @param \Domain\Billing\DwsBillingStatementContract $contract
     * @return static
     */
    public static function from(
        DwsBilling $billing,
        DwsBillingBundle $bundle,
        DwsBillingUser $user,
        DwsBillingStatementContract $contract
    ): self {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            dwsNumber: $user->dwsNumber,
            dwsGrantedServiceCode: $contract->dwsGrantedServiceCode,
            grantedAmount: $contract->grantedAmount,
            agreedOn: $contract->agreedOn,
            expiredOn: $contract->expiredOn,
            indexNumber: $contract->indexNumber,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->dwsGrantedServiceCode->value(),
            $this->grantedAmount(),
            $this->agreedOn->format(self::FORMAT_DATE),
            $this->expiredOn?->format(self::FORMAT_DATE) ?? '',
            $this->indexNumber,
        ];
    }

    /**
     * 契約支給量（分単位）を CSV 向けに変換する.
     *
     * 交換情報 CSV においては契約支給量を時単位かつ整数部3桁＋小数部2桁の5桁の整数で表す.
     * 例）6,030 minutes ＝ 100.5 hours ＝ 10050
     *
     * @return string
     */
    private function grantedAmount(): string
    {
        $minutes = $this->grantedAmount;
        return sprintf('%05d', Math::floor($minutes / 60 * 100));
    }
}

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
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;

/**
 * 障害：介護給付費等明細書：明細情報レコード.
 */
final class DwsBillingStatementItemRecord extends DwsBillingStatementRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingStatementItemRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
     * @param int $unitScore 単位数
     * @param int $count 回数
     * @param int $totalScore サービス単位数
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        #[JsonIgnore] public readonly ServiceCode $serviceCode,
        #[JsonIgnore] public readonly int $unitScore,
        #[JsonIgnore] public readonly int $count,
        #[JsonIgnore] public readonly int $totalScore
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_ITEM,
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
     * @param \Domain\Billing\DwsBillingStatementItem $item
     * @return static
     */
    public static function from(
        DwsBilling $billing,
        DwsBillingBundle $bundle,
        DwsBillingUser $user,
        DwsBillingStatementItem $item
    ): self {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            dwsNumber: $user->dwsNumber,
            serviceCode: $item->serviceCode,
            unitScore: $item->unitScore,
            count: $item->count,
            totalScore: $item->totalScore,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->serviceCode->toString(),
            $this->unitScore,
            $this->count,
            $this->totalScore,
            self::UNUSED,
        ];
    }
}

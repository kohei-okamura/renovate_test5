<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Common\Carbon;
use ScalikePHP\Seq;

/**
 * 障害：利用者負担上限額管理結果票：明細情報レコード.
 */
final class DwsBillingCopayCoordinationItemRecord extends DwsBillingCopayCoordinationRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingCopayCoordinationItemRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $copayCoordinationOfficeCode 上限額管理事業所番号
     * @param string $dwsNumber 受給者証番号
     * @param int $itemNumber 項番
     * @param string $officeCode 事業所番号
     * @param int $fee 利用者負担額集計・調整欄：総費用額
     * @param int $copay 利用者負担額集計・調整欄：利用者負担額
     * @param int $coordinatedCopay 利用者負担額集計・調整欄：管理結果後利用者負担額
     */
    public function __construct(
        Carbon $providedIn,
        #[JsonIgnore] public readonly string $cityCode,
        #[JsonIgnore] public readonly string $copayCoordinationOfficeCode,
        #[JsonIgnore] public readonly string $dwsNumber,
        #[JsonIgnore] public readonly int $itemNumber,
        #[JsonIgnore] public readonly string $officeCode,
        #[JsonIgnore] public readonly int $fee,
        #[JsonIgnore] public readonly int $copay,
        #[JsonIgnore] public readonly int $coordinatedCopay
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_ITEM,
            providedIn: $providedIn
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->cityCode,
            $this->copayCoordinationOfficeCode,
            $this->dwsNumber,
            $this->itemNumber,
            $this->officeCode,
            $this->fee,
            $this->copay,
            $this->coordinatedCopay,
        ];
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingCopayCoordination $copayCoordination
     * @return \ScalikePHP\Seq&static[]
     */
    public static function from(DwsBillingBundle $bundle, DwsBillingCopayCoordination $copayCoordination): Seq
    {
        return Seq::fromArray($copayCoordination->items)->map(
            fn (DwsBillingCopayCoordinationItem $item): self => new self(
                providedIn: $bundle->providedIn,
                cityCode: $bundle->cityCode,
                copayCoordinationOfficeCode: $copayCoordination->office->code,
                dwsNumber: $copayCoordination->user->dwsNumber,
                itemNumber: $item->itemNumber,
                officeCode: $item->office->code,
                fee: $item->subtotal->fee,
                copay: $item->subtotal->copay,
                coordinatedCopay: $item->subtotal->coordinatedCopay,
            )
        );
    }
}

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
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;

/**
 * 障害：介護給付費等明細書：日数情報レコード.
 */
final class DwsBillingStatementDaysRecord extends DwsBillingStatementRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingStatementDaysRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode サービス種類コード
     * @param \Domain\Common\Carbon $startedOn サービス開始日等：開始年月日
     * @param null|\Domain\Common\Carbon $terminatedOn サービス開始日等：終了年月日
     * @param int $serviceDays サービス開始日等：利用日数
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        #[JsonIgnore] public readonly DwsServiceDivisionCode $serviceDivisionCode,
        #[JsonIgnore] public readonly Carbon $startedOn,
        #[JsonIgnore] public readonly ?Carbon $terminatedOn,
        #[JsonIgnore] public readonly int $serviceDays
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_DAYS,
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
     * @param \Domain\Billing\DwsBillingStatementAggregate $aggregate
     * @return static
     */
    public static function from(
        DwsBilling $billing,
        DwsBillingBundle $bundle,
        DwsBillingUser $user,
        DwsBillingStatementAggregate $aggregate
    ): self {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            dwsNumber: $user->dwsNumber,
            serviceDivisionCode: $aggregate->serviceDivisionCode,
            startedOn: $aggregate->startedOn,
            terminatedOn: $aggregate->terminatedOn,
            serviceDays: $aggregate->serviceDays,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->serviceDivisionCode,
            $this->startedOn->format(self::FORMAT_DATE),
            $this->terminatedOn === null ? '' : $this->terminatedOn->format(self::FORMAT_DATE),
            $this->serviceDays,
            self::UNUSED,
            self::UNUSED,
        ];
    }
}

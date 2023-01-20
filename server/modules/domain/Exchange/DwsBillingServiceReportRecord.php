<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Common\Carbon;

/**
 * 障害：サービス提供実績記録票レコード.
 */
abstract class DwsBillingServiceReportRecord extends DwsDataRecord
{
    /** @var string レコード種別コード：基本情報レコード */
    protected const RECORD_FORMAT_SUMMARY = '01';

    /** @var string レコード種別コード：明細情報レコード */
    protected const RECORD_FORMAT_ITEM = '02';

    /**
     * {@link \Domain\Exchange\DwsBillingServiceReportRecord} constructor.
     *
     * @param string $recordFormat レコード種別コード
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param \Domain\Billing\DwsBillingServiceReportFormat $format 様式種別番号
     */
    public function __construct(
        #[JsonIgnore] public readonly string $recordFormat,
        #[JsonIgnore] public readonly Carbon $providedIn,
        #[JsonIgnore] public readonly string $cityCode,
        #[JsonIgnore] public readonly string $officeCode,
        #[JsonIgnore] public readonly string $dwsNumber,
        #[JsonIgnore] public readonly DwsBillingServiceReportFormat $format
    ) {
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // 交換情報識別番号
            self::RECORD_CATEGORY_DWS_BILLING_SERVICE_REPORT,
            // レコード種別コード
            $this->recordFormat,
            // サービス提供年月
            self::formatYearMonth($this->providedIn),
            // 市町村番号
            $this->cityCode,
            // 事業所番号
            $this->officeCode,
            // 受給者番号
            $this->dwsNumber,
            // 様式種別番号
            $this->format->value(),
        ];
    }
}

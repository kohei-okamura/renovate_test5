<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Common\Carbon;

/**
 * 障害：介護給付費等請求書レコード.
 */
abstract class DwsBillingInvoiceRecord extends DwsDataRecord
{
    /** @var string レコード種別コード：基本情報レコード */
    protected const RECORD_FORMAT_SUMMARY = '01';

    /** @var string レコード種別コード：明細情報レコード */
    protected const RECORD_FORMAT_ITEM = '02';

    /**
     * {@link \Domain\Exchange\DwsBillingInvoiceRecord} constructor.
     *
     * @param string $recordFormat レコード種別コード
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     */
    public function __construct(
        #[JsonIgnore] public readonly string $recordFormat,
        #[JsonIgnore] public readonly Carbon $providedIn,
        #[JsonIgnore] public readonly string $cityCode,
        #[JsonIgnore] public readonly string $officeCode
    ) {
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            self::RECORD_CATEGORY_DWS_BILLING_STATEMENT,
            $this->recordFormat,
            $this->providedIn->format(self::FORMAT_YEAR_MONTH),
            $this->cityCode,
            $this->officeCode,
        ];
    }
}

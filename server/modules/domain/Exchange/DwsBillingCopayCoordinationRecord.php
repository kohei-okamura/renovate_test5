<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Common\Carbon;

/**
 * 障害：利用者負担上限額管理結果票レコード.
 */
abstract class DwsBillingCopayCoordinationRecord extends DwsDataRecord
{
    /** @var string レコード種別コード：基本情報レコード */
    protected const RECORD_FORMAT_SUMMARY = '01';

    /** @var string レコード種別コード：明細情報レコード */
    protected const RECORD_FORMAT_ITEM = '02';

    /**
     * {@link \Domain\Exchange\DwsBillingCopayCoordinationRecord} constructor.
     *
     * @param string $recordFormat レコード種別コード
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     */
    public function __construct(
        #[JsonIgnore] public readonly string $recordFormat,
        #[JsonIgnore] public readonly Carbon $providedIn
    ) {
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            self::RECORD_CATEGORY_DWS_BILLING_COPAY_COORDINATION,
            $this->recordFormat,
            $this->providedIn->format(self::FORMAT_YEAR_MONTH),
        ];
    }
}

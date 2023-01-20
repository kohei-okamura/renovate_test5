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
 * 介護保険サービス：伝送：データレコード：介護給付費明細書.
 */
abstract class LtcsBillingStatementRecord extends LtcsDataRecord
{
    /** @var string レコード種別コード：基本情報レコード */
    protected const RECORD_FORMAT_SUMMARY = '01';

    /** @var string レコード種別コード：明細情報レコード */
    protected const RECORD_FORMAT_ITEM = '02';

    /** @var string レコード種別コード：集計情報レコード */
    protected const RECORD_FORMAT_AGGREGATE = '10';

    /**
     * {@link \Domain\Exchange\LtcsBillingStatementRecord} constructor.
     *
     * @param string $recordFormat レコード種別コード
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $officeCode 事業所番号
     * @param string $insurerNumber 証記載保険者番号
     * @param string $insNumber 被保険者番号
     */
    public function __construct(
        #[JsonIgnore] public readonly string $recordFormat,
        #[JsonIgnore] public readonly Carbon $providedIn,
        #[JsonIgnore] public readonly string $officeCode,
        #[JsonIgnore] public readonly string $insurerNumber,
        #[JsonIgnore] public readonly string $insNumber
    ) {
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // 交換情報識別番号
            self::RECORD_CATEGORY_LTCS_BILLING_DETAIL,
            // レコード種別コード
            $this->recordFormat,
            // サービス提供年月
            self::formatYearMonth($this->providedIn),
            // 事業所番号
            $this->officeCode,
            // 証記載保険者番号
            $this->insurerNumber,
            // 被保険者番号
            $this->insNumber,
        ];
    }
}

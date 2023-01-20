<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

/**
 * 介保：データレコード.
 */
abstract class LtcsDataRecord extends DataRecord
{
    /** @var string 交換情報識別番号：介護給付費請求書情報 */
    public const RECORD_CATEGORY_LTCS_BILLING_STATEMENT = '7111';

    /** @var string 交換情報識別番号：居宅介護（支援）給付費請求明細書情報 */
    public const RECORD_CATEGORY_LTCS_BILLING_DETAIL = '7131';

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $recordNumber,
        ];
    }
}

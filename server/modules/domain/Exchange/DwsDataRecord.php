<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

/**
 * 障害：データレコード.
 */
abstract class DwsDataRecord extends DataRecord
{
    /** @var string 交換情報識別番号：介護給付費・訓練等給付費等請求書情報 */
    public const RECORD_CATEGORY_DWS_BILLING_STATEMENT = 'J111';

    /** @var string 交換情報識別番号：介護給付費・訓練等給付費等明細書情報 */
    public const RECORD_CATEGORY_DWS_BILLING_DETAIL = 'J121';

    /** @var string 交換情報識別番号：利用者負担上限額管理結果票情報 */
    public const RECORD_CATEGORY_DWS_BILLING_COPAY_COORDINATION = 'J411';

    /** @var string 交換情報識別番号：サービス提供実績記録票情報 */
    public const RECORD_CATEGORY_DWS_BILLING_SERVICE_REPORT = 'J611';

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $recordNumber,
        ];
    }
}

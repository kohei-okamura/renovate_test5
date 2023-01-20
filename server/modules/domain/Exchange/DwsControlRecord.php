<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\DwsBilling;
use Domain\Common\Carbon;

/**
 * 障害：コントロールレコード.
 */
final class DwsControlRecord extends ControlRecord
{
    /** @var int データ種別：介護給付費・訓練等給付費等請求書情報 */
    private const DATA_TYPE_BILLING_INVOICE = 'J11';

    /** @var int データ種別：利用者負担上限額管理結果票情報 */
    private const DATA_TYPE_BILLING_COPAY_COORDINATION = 'J41';

    /** @var int データ種別：サービス提供実績記録票情報 */
    private const DATA_TYPE_BILLING_SERVICE_REPORT = 'J61';

    /** @var int 媒体：伝送 */
    private const MEDIA_TRANSMISSION = 1;

    /**
     * {@link \Domain\Exchange\DwsControlRecord} constructor.
     *
     * @property-read int $recordCount レコード件数
     * @property-read string $officeCode 事業所番号
     * @property-read string $dataType データ種別
     * @property-read \Domain\Common\Carbon $transactedIn 処理対象年月
     * @param #[JsonIgnore]publicreadonlyint $recordCount
     * @param #[JsonIgnore]publicreadonlystring $officeCode
     * @param #[JsonIgnore]publicreadonlystring $dataType
     * @param #[JsonIgnore]publicreadonlyCarbon $transactedIn
     */
    public function __construct(
        #[JsonIgnore] public readonly int $recordCount,
        #[JsonIgnore] public readonly string $officeCode,
        #[JsonIgnore] public readonly string $dataType,
        #[JsonIgnore] public readonly Carbon $transactedIn
    ) {
    }

    /**
     * 介護給付費・訓練等給付費等請求書情報のコントロールレコードを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param int $recordCount
     * @return static
     */
    public static function forInvoice(DwsBilling $billing, int $recordCount): self
    {
        return new self(
            recordCount: $recordCount,
            officeCode: $billing->office->code,
            dataType: self::DATA_TYPE_BILLING_INVOICE,
            transactedIn: $billing->transactedIn,
        );
    }

    /**
     * 利用者負担上限額管理結果票情報のコントロールレコードを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param int $recordCount
     * @return static
     */
    public static function forCopayCoordination(DwsBilling $billing, int $recordCount): self
    {
        return new self(
            recordCount: $recordCount,
            officeCode: $billing->office->code,
            dataType: self::DATA_TYPE_BILLING_COPAY_COORDINATION,
            transactedIn: $billing->transactedIn,
        );
    }

    /**
     * サービス提供実績記録票のコントロールレコードを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param int $recordCount
     * @return static
     */
    public static function forServiceReport(DwsBilling $billing, int $recordCount): self
    {
        return new self(
            recordCount: $recordCount,
            officeCode: $billing->office->code,
            dataType: self::DATA_TYPE_BILLING_SERVICE_REPORT,
            transactedIn: $billing->transactedIn,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // レコード番号 1: 常に先頭
            '1',
            // ボリューム番号 0: 単独ファイル
            '0',
            // データレコードの件数
            (string)$this->recordCount,
            // データ種別
            $this->dataType,
            // 市町村番号 0: 市町村でない
            '0',
            // 事業所番号
            $this->officeCode,
            // 都道府県番号 0: 都道府県でない
            '0',
            // 媒体区分
            self::MEDIA_TRANSMISSION,
            // 処理対象年月 伝送を行う年月
            $this->transactedIn->format(self::FORMAT_YEAR_MONTH),
            // 予備
            self::RESERVED,
        ];
    }
}

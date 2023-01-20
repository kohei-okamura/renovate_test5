<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\LtcsBilling;
use Domain\Common\Carbon;

/**
 * 介護保険サービス：伝送：コントロールレコード.
 */
final class LtcsControlRecord extends ControlRecord
{
    /** @var int 媒体：伝送（インターネット） */
    public const MEDIA_TRANSMISSION = 7;

    /** @var int データ種別：介護給付費請求書情報 */
    private const DATA_TYPE_BILLING = '711';

    /**
     * {@link \Domain\Exchange\LtcsControlRecord} constructor.
     *
     * @param int $recordCount レコード件数
     * @param string $officeCode 事業所番号
     * @param \Domain\Common\Carbon $transactedIn 処理対象年月
     */
    public function __construct(
        #[JsonIgnore] public readonly int $recordCount,
        #[JsonIgnore] public readonly string $officeCode,
        #[JsonIgnore] public readonly Carbon $transactedIn
    ) {
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param int $recordCount
     * @return self
     */
    public static function from(LtcsBilling $billing, int $recordCount): self
    {
        return new self(
            recordCount: $recordCount,
            officeCode: $billing->office->code,
            transactedIn: $billing->transactedIn,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // レコード番号（連番）
            $recordNumber,
            // ボリューム通番：常に単独ファイルとするため固定値で 0 を設定する.
            0,
            // レコード件数
            $this->recordCount,
            // データ種別：固定値
            self::DATA_TYPE_BILLING,
            // 福祉事務所特定番号：福祉事務所ではないため固定値で 0 を設定する.
            0,
            // 保険者番号：保険者ではないため固定値で 0 を設定する.
            0,
            // 事業所番号
            $this->officeCode,
            // 都道府県番号：都道府県ではないため固定値で 0 を設定する.
            0,
            // 媒体区分：常にインターネット伝送を想定するため固定値を設定する.
            self::MEDIA_TRANSMISSION,
            // 処理対象年月
            self::formatYearMonth($this->transactedIn),
            // ファイル管理番号：常に単独ファイルとするため固定値で 0 を設定する.
            0,
        ];
    }
}

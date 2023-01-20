<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Model;
use ScalikePHP\Seq;

/**
 * 全銀レコード：トレーラレコード.
 *
 * @property-read int $totalCount 合計件数
 * @property-read int $totalAmount 合計金額
 * @property-read int $succeededCount 振替済件数
 * @property-read int $succeededAmount 振替済金額
 * @property-read int $failedCount 振替不能件数
 * @property-read int $failedAmount 振替不能金額
 */
final class ZenginTrailerRecord extends Model
{
    use ZenginRecordSupport;

    private const FORMAT = [
        // データ区分
        'dataType' => 1,
        // 合計件数
        'totalCount' => 6,
        // 合計金額
        'totalAmount' => 12,
        // 振替済件数
        'succeededCount' => 6,
        // 振替済金額
        'succeededAmount' => 12,
        // 振替不能件数
        'failedCount' => 6,
        // 振替不能金額
        'failedAmount' => 12,
        // ダミー
        'dummy' => 65,
    ];

    /**
     * @param \Domain\UserBilling\ZenginDataRecord[]|\ScalikePHP\Seq $dataRecords
     * @return static
     */
    public static function from(Seq $dataRecords): self
    {
        return self::create([
            'totalCount' => $dataRecords->count(),
            'totalAmount' => $dataRecords->map(fn (ZenginDataRecord $x) => $x->amount)->sum(),
            'succeededCount' => 0,
            'succeededAmount' => 0,
            'failedCount' => 0,
            'failedAmount' => 0,
        ]);
    }

    /**
     * レコード文字列からインスタンスを生成する.
     *
     * @param string $recordString
     * @return static
     */
    public static function parse(string $recordString): self
    {
        $attrs = self::parseRecordString($recordString, self::FORMAT);
        return self::create([
            'totalCount' => self::parseNumeric($attrs['totalCount']),
            'totalAmount' => self::parseNumeric($attrs['totalAmount']),
            'succeededCount' => self::parseNumeric($attrs['succeededCount']),
            'succeededAmount' => self::parseNumeric($attrs['succeededAmount']),
            'failedCount' => self::parseNumeric($attrs['failedCount']),
            'failedAmount' => self::parseNumeric($attrs['failedAmount']),
        ]);
    }

    /**
     * 全銀レコード文字列（UTF-8）に変換する.
     *
     * @return string
     */
    public function toRecordString(): string
    {
        $values = [
            // データ区分
            '8',
            // 合計件数
            self::pad($this->totalCount, self::FORMAT['totalCount'], '0', \STR_PAD_LEFT),
            // 合計金額
            self::pad($this->totalAmount, self::FORMAT['totalAmount'], '0', \STR_PAD_LEFT),
            // 振替済件数
            self::pad($this->succeededCount, self::FORMAT['succeededCount'], '0', \STR_PAD_LEFT),
            // 振替済金額
            self::pad($this->succeededAmount, self::FORMAT['succeededAmount'], '0', \STR_PAD_LEFT),
            // 振替不能件数
            self::pad($this->failedCount, self::FORMAT['failedCount'], '0', \STR_PAD_LEFT),
            // 振替不能金額
            self::pad($this->failedAmount, self::FORMAT['failedAmount'], '0', \STR_PAD_LEFT),
            // ダミー
            str_repeat(' ', self::FORMAT['dummy']),
        ];
        return implode('', $values);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'totalCount',
            'totalAmount',
            'succeededCount',
            'succeededAmount',
            'failedCount',
            'failedAmount',
        ];
    }
}

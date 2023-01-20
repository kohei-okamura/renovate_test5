<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Common\Carbon;
use Domain\Model;
use Lib\Exceptions\InvalidArgumentException;
use Lib\KanaConverter;

/**
 * 全銀レコード：ヘッダーレコード.
 *
 * @property-read string $bankingClientCode 委託者コード
 * @property-read string $bankingClientName 委託者名
 * @property-read \Domain\Common\Carbon $deductedOn 引落日
 */
final class ZenginHeaderRecord extends Model
{
    use ZenginRecordSupport;

    private const FORMAT = [
        // データ区分
        'dataType' => 1,
        // 種別コード
        'typeCode' => 2,
        // コード区分
        'codeType' => 1,
        // 委託者コード
        'bankingClientCode' => 10,
        // 委託者名
        'bankingClientName' => 40,
        // 引落日
        'deductedOn' => 4,
        // 取引銀行番号
        'bankNumber' => 4,
        // 取引銀行名
        'bankName' => 15,
        // 取引支店番号
        'bankBranchNumber' => 3,
        // 取引支店名
        'bankBranchName' => 15,
        // 預金種目（委託者）
        'bankAccountType' => 1,
        // 口座番号（委託者）
        'bankAccountNumber' => 7,
        // ダミー
        'dummy' => 17,
    ];

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\UserBilling\WithdrawalTransaction $withdrawalTransaction
     * @param string $bankingClientName
     * @param \Domain\Common\Carbon $deductedOn
     * @return \Domain\UserBilling\ZenginHeaderRecord
     * @noinspection PhpUnusedParameterInspection
     */
    public static function from(
        WithdrawalTransaction $withdrawalTransaction,
        string $bankingClientName,
        Carbon $deductedOn
    ): self {
        return self::create([
            'bankingClientCode' => mb_substr($withdrawalTransaction->items[0]->zenginRecord->clientNumber, 0, 10),
            // [FYI]
            // 事業者名をそのまま使うと漢字が混入してしまう
            // 事業者別設定などで全銀ファイル用の事業者名（委託者名）が設定可能となるまで一旦固定値にしてしのぐ
            'bankingClientName' => KanaConverter::toUppercaseHalfWidthKatakana('ユースタイルラボラトリーカブシキガイシャ'),
            // 'bankingClientName' => KanaConverter::toUppercaseHalfWidthKatakana($bankingClientName),
            'deductedOn' => $deductedOn,
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
            'bankingClientCode' => $attrs['bankingClientCode'],
            'bankingClientName' => $attrs['bankingClientName'],
            'deductedOn' => self::parseDeductedOn($attrs['deductedOn']),
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
            '1',
            // 種別コード
            '91',
            // コード区分
            '1',
            // 委託者コード
            self::pad($this->bankingClientCode, self::FORMAT['bankingClientCode'], '0', \STR_PAD_LEFT),
            // 委託者名
            self::pad($this->bankingClientName, self::FORMAT['bankingClientName'], ' ', \STR_PAD_RIGHT),
            // 引落日
            $this->deductedOn->format('md'),
            // 取引銀行番号
            str_repeat(' ', self::FORMAT['bankNumber']),
            // 取引銀行名
            str_repeat(' ', self::FORMAT['bankName']),
            // 取引支店番号
            str_repeat(' ', self::FORMAT['bankBranchNumber']),
            // 取引支店名
            str_repeat(' ', self::FORMAT['bankBranchName']),
            // 預金種目（委託者）
            str_repeat(' ', self::FORMAT['bankAccountType']),
            // 口座番号（委託者）
            str_repeat(' ', self::FORMAT['bankAccountNumber']),
            // ダミー
            str_repeat(' ', self::FORMAT['dummy']),
        ];
        return implode('', $values);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'bankingClientCode',
            'bankingClientName',
            'deductedOn',
        ];
    }

    /**
     * 月日から引落とし年月日を取得する
     *
     * @param string $monthDay
     * @return \Domain\Common\Carbon
     */
    private static function parseDeductedOn(string $monthDay): Carbon
    {
        if (!(bool)preg_match('/\A(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])\z/u', $monthDay)) {
            throw new InvalidArgumentException("{$monthDay} is not date format");
        }
        $month = (int)substr($monthDay, 0, 2);
        $day = (int)substr($monthDay, 2, 2);
        $year = Carbon::now()->year;
        $date = Carbon::create($year, $month, $day);
        // 引き落とし日は常に過去になるため現在日時より未来の場合は昨年
        // 2月29日の場合にうるう年以外だと3月1日となり1日ずれるため subYear を使用せず生成し直す
        return $date->isFuture() ? Carbon::create($year - 1, $month, $day) : $date;
    }
}

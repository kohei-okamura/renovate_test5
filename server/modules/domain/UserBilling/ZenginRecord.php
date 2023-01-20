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
use ScalikePHP\Seq;

/**
 * 全銀レコード.
 *
 * @property-read \Domain\UserBilling\ZenginHeaderRecord $header ヘッダーレコード
 * @property-read \Domain\UserBilling\ZenginDataRecord[] $data データレコード
 * @property-read \Domain\UserBilling\ZenginTrailerRecord $trailer トレーラレコード
 */
final class ZenginRecord extends Model
{
    private const DATA_TYPE_HEADER = 1;
    private const DATA_TYPE_DATA = 2;
    private const DATA_TYPE_TRAILER = 8;
    private const DATA_TYPE_END = 9;

    private const RECORD_LENGTH = 120;

    /**
     * 全銀レコードを生成する.
     *
     * @param \Domain\UserBilling\WithdrawalTransaction $withdrawalTransaction
     * @param string $bankingClientName
     * @param \Domain\Common\Carbon $deductedOn
     * @return \Domain\UserBilling\ZenginRecord
     */
    public static function from(
        WithdrawalTransaction $withdrawalTransaction,
        string $bankingClientName,
        Carbon $deductedOn
    ): self {
        $dataRecords = Seq::from(...$withdrawalTransaction->items)
            ->map(fn (WithdrawalTransactionItem $item): ZenginDataRecord => $item->zenginRecord);
        return self::create([
            'header' => ZenginHeaderRecord::from($withdrawalTransaction, $bankingClientName, $deductedOn),
            'data' => $dataRecords->toArray(),
            'trailer' => ZenginTrailerRecord::from($dataRecords),
        ]);
    }

    /**
     * 全銀ファイルの内容（文字列）からインスタンスを生成する.
     *
     * @param string $content
     * @return static
     */
    public static function parse(string $content): self
    {
        // FYI: Builder パターンの適用を検討したが循環参照が発生するため採用しなかった
        $header = null;
        $data = [];
        $trailer = null;
        foreach (self::parseContent($content) as $record) {
            $type = (int)substr($record, 0, 1);
            switch ($type) {
                case self::DATA_TYPE_HEADER:
                    $header = ZenginHeaderRecord::parse($record);
                    break;
                case self::DATA_TYPE_DATA:
                    $data[] = ZenginDataRecord::parse($record);
                    break;
                case self::DATA_TYPE_TRAILER:
                    $trailer = ZenginTrailerRecord::parse($record);
                    break;
                case self::DATA_TYPE_END:
                    // Nothing to do.
                    break;
                default:
                    throw new InvalidArgumentException("Failed to parse: unexpected data type {$type}");
            }
        }
        if ($header === null) {
            throw new InvalidArgumentException('Failed to parse: header record is not detected');
        }
        if ($trailer === null) {
            throw new InvalidArgumentException('Failed to parse: header record is not detected');
        }
        $attrs = compact('header', 'data', 'trailer');
        return self::create($attrs);
    }

    /**
     * 全銀レコードを文字列に変換する.
     *
     * @return string
     */
    public function toZenginRecordString(): string
    {
        $xs = Seq::from(
            ...$this->buildHeaderRecords(),
            ...$this->buildDataRecords(),
            ...$this->buildTrailerRecords(),
            ...$this->buildEndRecords(),
        );
        return $xs->mkString("\r\n");
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'header',
            'data',
            'trailer',
        ];
    }

    /**
     * 全銀ファイルの内容（文字列）をレコード単位（≒行単位）に分割する.
     *
     * @param string $content
     * @return string[]
     */
    private static function parseContent(string $content): array
    {
        $sanitized = str_replace(["\r\n", "\r", "\n"], '', $content);
        $records = str_split($sanitized, self::RECORD_LENGTH);
        return mb_convert_encoding($records, 'utf-8', 'cp932');
    }

    /**
     * ヘッダーレコードを文字列に変換する.
     *
     * @return string[]
     */
    private function buildHeaderRecords(): iterable
    {
        yield $this->header->toRecordString();
    }

    /**
     * データレコードを文字列に変換する.
     *
     * @return string[]
     */
    private function buildDataRecords(): iterable
    {
        yield from Seq::from(...$this->data)->map(fn (ZenginDataRecord $x): string => $x->toRecordString());
    }

    /**
     * トレーラレコードを文字列に変換する.
     *
     * @return string[]
     */
    private function buildTrailerRecords(): iterable
    {
        yield $this->trailer->toRecordString();
    }

    /**
     * エンドレコードを文字列に変換する.
     *
     * @return string[]
     */
    private function buildEndRecords(): iterable
    {
        yield '9' . str_repeat(' ', 119);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Csv;

/**
 * CSV 行モデル.
 */
abstract class CsvRow
{
    private array $values;

    /**
     * {@link \Domain\Csv\CsvRow} constructor.
     *
     * @param array|string[] $values
     */
    private function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * インスタンスを生成する.
     *
     * @param array $values
     * @return static
     */
    public static function create(array $values): self
    {
        return new static($values);
    }

    /**
     * 列（真偽値）を取得する.
     *
     * @param int $index
     * @return bool
     */
    protected function getBoolean(int $index): bool
    {
        return (bool)$this->getInteger($index);
    }

    /**
     * 列（整数）を取得する.
     *
     * @param int $index
     * @return int
     */
    protected function getInteger(int $index): int
    {
        return (int)$this->values[$index];
    }

    /**
     * 列（文字列）を取得する.
     *
     * @param int $index
     * @return string
     */
    protected function getString(int $index): string
    {
        return $this->values[$index];
    }
}

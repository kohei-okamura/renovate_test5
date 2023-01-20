<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib\StreamFilter;

use ScalikePHP\Seq;
use Traversable;

/**
 * フィルターストリームパス生成クラス.
 */
final class StreamFilterPathBuilder
{
    private string $resource;
    private array $readFilters;
    private array $writeFilters;

    /**
     * StreamFilterPathBuilder constructor.
     *
     * @param string $resource
     * @param array $readFilters
     * @param array $writeFilters
     */
    private function __construct(string $resource, array $readFilters, array $writeFilters)
    {
        $this->resource = $resource;
        $this->readFilters = $readFilters;
        $this->writeFilters = $writeFilters;
    }

    /**
     * 新しいインスタンスを生成する.
     */
    public static function create()
    {
        return new static('', [], []);
    }

    /**
     * パスを生成する.
     *
     * @return string
     */
    public function build(): string
    {
        return 'php://' . Seq::fromTraversable($this->segments())->mkString('/');
    }

    /**
     * リソース（ファイル）を設定する.
     *
     * @param string $resource
     * @return static
     */
    public function withResource(string $resource): self
    {
        return new static($resource, $this->readFilters, $this->writeFilters);
    }

    /**
     * 読み込みフィルタを追加する.
     *
     * @param string $filter
     * @return static
     */
    public function withReadFilter(string $filter): self
    {
        return new static($this->resource, [...$this->readFilters, $filter], $this->writeFilters);
    }

    /**
     * 書き込みフィルタを追加する.
     *
     * @param string $filter
     * @return static
     */
    public function withWriteFilter(string $filter): self
    {
        return new static($this->resource, $this->readFilters, [...$this->writeFilters, $filter]);
    }

    /**
     * パスのセグメント一覧を取得する.
     *
     * @return string[]|\Traversable
     */
    private function segments(): Traversable
    {
        yield 'filter';
        foreach ($this->readFilters as $filter) {
            yield "read={$filter}";
        }
        foreach ($this->writeFilters as $filter) {
            yield "write={$filter}";
        }
        yield "resource={$this->resource}";
    }
}

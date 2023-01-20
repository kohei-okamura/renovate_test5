<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\File;

use Lib\RandomString;

/**
 * ファイル入力ストリーム.
 */
final class FileInputStream
{
    private const FILE_NAME_LENGTH = 40;
    private string $name;
    private string $source;

    /** @var resource */
    private $stream;

    /**
     * FileInputStream constructor.
     *
     * @param string $name ファイル名
     * @param string $source 入力元ファイルパス
     * @param resource $stream 入力ストリーム
     */
    private function __construct(string $name, string $source, $stream)
    {
        $this->name = $name;
        $this->source = $source;
        $this->stream = $stream;
    }

    /**
     * Create an instance.
     *
     * @param string $name ファイル名
     * @param string $source 入力元ファイルパス
     * @return static
     */
    public static function from(string $name, string $source): self
    {
        return new static($name, $source, fopen($source, 'rb'));
    }

    /**
     * Create an instance from {@link \Illuminate\Http\File} or {@link \Illuminate\Http\UploadedFile}.
     *
     * @param \SplFileInfo $file
     * @return static
     */
    public static function fromFile($file): self
    {
        return self::from(RandomString::generate(self::FILE_NAME_LENGTH, RandomString::DEFAULT_TABLE), $file->getRealPath());
    }

    /**
     * ファイル名を取得する.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * 入力元ファイルパスを取得する.
     *
     * @return string
     */
    public function source(): string
    {
        return $this->source;
    }

    /**
     * ファイル入力ストリーム（リソース）を取得する.
     *
     * @return resource
     */
    public function stream()
    {
        return $this->stream;
    }
}

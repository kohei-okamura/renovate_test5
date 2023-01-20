<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

use Generator;
use ScalikePHP\Seq;
use SplFileObject;

/**
 * CSV Reader/Writer.
 */
final class Csv
{
    /**
     * CSV ファイルを読み込む.
     *
     * @param string $path
     * @return \ScalikePHP\Seq
     */
    public static function read(string $path): Seq
    {
        $g = call_user_func(function () use ($path): Generator {
            $file = new SplFileObject($path);
            try {
                $file->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::READ_CSV);
                foreach ($file as $row) {
                    if (!$file->eof()) {
                        yield $file->key() => $row;
                    }
                }
            } finally {
                unset($file);
            }
        });
        return Seq::fromTraversable($g);
    }

    /**
     * CSV ファイルを書き込む.
     *
     * @param string $path
     * @param iterable $rows
     * @return void
     */
    public static function write(string $path, iterable $rows): void
    {
        $file = new SplFileObject($path, 'w');
        try {
            foreach ($rows as $row) {
                $file->fputcsv($row);
            }
        } finally {
            unset($file);
        }
    }
}

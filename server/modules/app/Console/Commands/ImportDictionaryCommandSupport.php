<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Lib\Exceptions\InvalidConsoleOptionException;

/**
 * 辞書インポート arguments サポート.
 */
trait ImportDictionaryCommandSupport
{
    /**
     * id を取得する.
     *
     * @return int
     */
    public function getId(): int
    {
        $id = $this->argument('id');
        if (preg_match('/\A[0-9]+\z/', $id) === 0) {
            throw new InvalidConsoleOptionException('id must be integer');
        }
        return (int)$id;
    }

    /**
     * filepath を取得する.
     *
     * @return string
     */
    public function getFilepath(): string
    {
        $filename = $this->argument('filename');
        if ($filename === null) {
            throw new InvalidConsoleOptionException('filename is required');
        }
        return $filename;
    }

    /**
     * effectivatedOn を取得する.
     *
     * @return string
     */
    public function getEffectivatedOn(): string
    {
        $effectivatedOn = $this->argument('effectivatedOn');
        if (preg_match('/\A20[0-9][0-9](\/|-)[0-1]*[0-9](\/|-)[0-3]*[0-9]\z/', $effectivatedOn) !== 1) {
            throw new InvalidConsoleOptionException('effectivatedOn is invalid format');
        }
        return $effectivatedOn;
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Config;

/**
 * 設定リポジトリ.
 */
interface Config
{
    /**
     * 設定値を取得する.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * filename を取得する.
     *
     * @param string $key
     * @return string
     */
    public function filename(string $key): string;
}

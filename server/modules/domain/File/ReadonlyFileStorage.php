<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\File;

use Domain\Common\Carbon;
use ScalikePHP\Option;

/**
 * 読み取り専用ファイル保管ストレージ.
 */
interface ReadonlyFileStorage
{
    /**
     * ファイルをストレージから取得する（ローカルに一時的に保存する）.
     *
     * @param string $path 対象ファイルのパス
     * @return \ScalikePHP\Option|\SplFileInfo[] 保存されたファイルの情報
     */
    public function fetch(string $path): Option;

    /**
     * ファイルをストレージから読み込む.
     *
     * @param string $path 対象ファイルのパス
     * @return resource[]|\ScalikePHP\Option
     */
    public function fetchStream(string $path): Option;

    /**
     * 一時的な URL を生成して返す.
     *
     * @param string $path 対象ファイルのパス
     * @param \Domain\Common\Carbon $expiration 有効期限
     * @param string $filename
     * @return string 一時的なURL
     */
    public function getTemporaryUrl(string $path, Carbon $expiration, string $filename): string;
}

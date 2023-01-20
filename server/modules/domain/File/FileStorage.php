<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\File;

use ScalikePHP\Option;

/**
 * ファイル保管ストレージ.
 */
interface FileStorage extends ReadonlyFileStorage
{
    /**
     * ファイルをストレージに保管する.
     *
     * @param string $dir 保管先ディレクトリ
     * @param \Domain\File\FileInputStream $inputStream 保存するファイルの情報
     * @return \ScalikePHP\Option|string[] 保管されたファイルのパス
     */
    public function store(string $dir, FileInputStream $inputStream): Option;
}

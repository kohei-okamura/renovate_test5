<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console;

use Closure;

/**
 * 事業者を横断して処理を実行する仕組み.
 */
interface OrganizationIterator
{
    /**
     * 事業者ごとに {@link \Domain\Context\Context} を生成してクロージャーを実行する.
     *
     * @param \Closure $f {@link \Domain\Context\Context} を引数に取るクロージャー
     * @return void
     */
    public function iterate(Closure $f): void;
}

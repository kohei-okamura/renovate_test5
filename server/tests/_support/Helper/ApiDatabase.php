<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Helper;

use Codeception\Module;

/**
 * API Test Helper Class for Database.
 */
class ApiDatabase extends Module
{
    /**
     * トランザクションを開始する.
     */
    public function haveTransaction(): void
    {
        app('db')->beginTransaction();
    }

    /**
     * トランザクションを終了する.
     */
    public function cleanUpTransaction(): void
    {
        app('db')->rollback();
    }
}

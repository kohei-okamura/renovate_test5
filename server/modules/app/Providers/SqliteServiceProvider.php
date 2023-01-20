<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use App\Database\SqliteConnector;
use Illuminate\Support\ServiceProvider;

/**
 * Sqlite Service Provider.
 *
 * @codeCoverageIgnore リクエスト受信〜APPに来るまでの処理なのでUnitTest除外
 */
final class SqliteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('db.connector.sqlite', SqliteConnector::class);
    }
}

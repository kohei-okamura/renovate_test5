<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use App\Support\BlueprintMixin;
use Illuminate\Support\ServiceProvider;

/**
 * Migration Service Provider.
 *
 * @codeCoverageIgnore DB設定処理なのでUnitTest除外.
 */
final class MigrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $mixin = app(BlueprintMixin::class);
        $mixin();
    }
}

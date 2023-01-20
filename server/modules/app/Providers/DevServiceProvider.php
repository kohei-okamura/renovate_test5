<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use App\Console\Commands\MakeCustomValidatorCommand;
use App\Console\Commands\MakeMixinCommand;
use App\Console\Commands\MakeUnitTestCommand;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Tinker\TinkerServiceProvider;

/**
 * Development Service Provider.
 *
 * @codeCoverageIgnore 開発環境用のProviderなのでUnitTest除外
 */
final class DevServiceProvider extends ServiceProvider
{
    private array $aliases = [
        '\Eloquent' => \Illuminate\Database\Eloquent\Model::class,
        '\Illuminate\Foundation\AliasLoader' => \App\Support\AliasLoader::class,
    ];

    public function boot(): void
    {
        $app = $this->app;
        if ($app->runningInConsole()) {
            foreach ($this->aliases as $alias => $original) {
                if (!class_exists($alias)) {
                    class_alias($original, $alias);
                }
            }
            $this->commands([
                MakeCustomValidatorCommand::class,
                MakeMixinCommand::class,
                MakeUnitTestCommand::class,
            ]);
            $app->register(IdeHelperServiceProvider::class);
            $app->register(TinkerServiceProvider::class);
        }
    }
}

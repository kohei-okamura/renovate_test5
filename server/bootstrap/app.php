<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Dotenv\Dotenv;
use Laravel\Lumen\Application;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;

require_once __DIR__ . '/../../vendor/autoload.php';

$root = dirname(__DIR__);

(new LoadEnvironmentVariables($root))->bootstrap();
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Tokyo'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Application($root);

$env = $app->environment();
if ($env !== 'production' && is_readable("{$root}/.env.{$env}")) {
    Dotenv::createMutable($root, ".env.{$env}")->load();
}

$app->configure('database');
$app->configure('dompdf');
$app->configure('filesystems');
$app->configure('logging');
$app->configure('horizon');
$app->configure('mail');
$app->configure('services');
$app->configure('session');
$app->configure('zinger');

$app->withEloquent();

$aliases = [
    'Carbon' => Carbon\CarbonImmutable::class,
];
foreach ($aliases as $alias => $original) {
    if (!class_exists($alias)) {
        class_alias($original, "\\{$alias}");
    }
}

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class);

// Lumen 標準のミドルウェアのコンストラクタで下記のインターフェースやクラスを注入しているが
// 標準のサービスプロバイダーではそれらの依存関係を定義していないためエラーが発生してしまう
// これを防ぐためエイリアスを用いて適切な依存関係を定義する
$app->alias('cookie', Illuminate\Contracts\Cookie\QueueingFactory::class);
$app->alias('session', Illuminate\Session\SessionManager::class);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    Illuminate\Session\Middleware\StartSession::class,
]);

$app->routeMiddleware([
    'authorize' => App\Http\Middleware\AuthorizeMiddleware::class,
]);
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\RequestServiceProvider::class);
$app->register(App\Providers\SqliteServiceProvider::class);
$app->register(App\Providers\TwilioServiceProvider::class);
$app->register(Barryvdh\DomPDF\ServiceProvider::class);
$app->register(Barryvdh\Snappy\LumenServiceProvider::class);
$app->register(Illuminate\Cookie\CookieServiceProvider::class);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Session\SessionServiceProvider::class);
$app->register(Sichikawa\LaravelSendgridDriver\MailServiceProvider::class);
// $app->register(Laravel\Horizon\HorizonServiceProvider::class); // TODO DEV-3092

$isLocal = $env === 'local';
$isTesting = $env === 'testing';
$isE2E = $env === 'e2e';
$isBillingTest = $env === 'billing';
$isDocker = $env === 'docker';
$isProduction = $env === 'production';
$isStaging = $env === 'staging';
$isConsole = $app->runningInConsole();

if ($isDocker || $isLocal) {
    $app->register(App\Providers\DevServiceProvider::class);
}
if ($isDocker || $isLocal || $isTesting || $isE2E || $isStaging || $isBillingTest) {
    $app->register(App\Providers\FakerServiceProvider::class);
}
if ($isLocal || $isE2E || $isBillingTest) {
    $app->register(App\Providers\QueryLogServiceProvider::class);
}
if ($isConsole) {
    $app->register(App\Providers\MigrationServiceProvider::class);
}

// See https://github.com/s-ichikawa/laravel-sendgrid-driver#install-lumen
unset($app->availableBindings['mailer']);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

require __DIR__ . '/../routes/web.php';

return $app;

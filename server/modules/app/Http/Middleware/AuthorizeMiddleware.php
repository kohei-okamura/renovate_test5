<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Auth\Authorize;
use App\Http\Requests\StaffRequest;
use Closure;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * Class AuthorizeMiddleware.
 * @codeCoverageIgnore MiddlewareはUnitTestでは実施しない
 */
class AuthorizeMiddleware
{
    /** @var string route middleware key name */
    protected static string $keyName = 'authorize';

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string ...$requiredPermission
     * @return mixed
     */
    public function handle($request, Closure $next, string ...$requiredPermission)
    {
        app()->call(function (StaffRequest $request, Authorize $authorize) use ($requiredPermission): void {
            $authorize->handle($request->context(), ...$requiredPermission);
        });

        return $next($request);
    }

    /**
     * route middleware key name と middleware パラメーターを返す.
     *
     * @param \Domain\Permission\Permission ...$permission
     * @return string
     */
    public static function with(Permission ...$permission): string
    {
        $permissions = Seq::from(...$permission)
            ->map(fn (Permission $x): string => $x->value())
            ->toArray();
        return static::$keyName . ':' . implode(',', $permissions);
    }
}

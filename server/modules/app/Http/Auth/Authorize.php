<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Auth;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use ScalikePHP\Seq;

/**
 * 認可
 */
class Authorize
{
    /**
     * 認可処理.
     *
     * @param \Domain\Context\Context $context
     * @param string ...$requiredPermission
     * @throws AuthorizationException
     */
    public function handle(Context $context, string ...$requiredPermission): void
    {
        $authorized = Seq::from(...$requiredPermission)
            ->map(fn (string $x): Permission => Permission::from($x))
            ->exists(fn (Permission $x): bool => $context->isAuthorizedTo($x));
        if (!$authorized) {
            throw new AuthorizationException();
        }
    }
}

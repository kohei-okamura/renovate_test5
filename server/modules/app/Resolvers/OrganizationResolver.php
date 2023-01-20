<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Resolvers;

use Laravel\Lumen\Http\Request as LumenRequest;
use ScalikePHP\Option;

/**
 * OrganizationResolver Interface.
 */
interface OrganizationResolver
{
    /**
     * リクエストから事業者を特定する.
     *
     * @param \Laravel\Lumen\Http\Request $request
     * @return \Domain\Organization\Organization[]|\ScalikePHP\Option
     */
    public function resolve(LumenRequest $request): Option;
}

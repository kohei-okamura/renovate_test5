<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Resolvers;

use App\Http\Requests\StaffRequest;
use ScalikePHP\Option;

/**
 * StaffResolver Interface.
 */
interface StaffResolver
{
    /**
     * リクエストからスタッフを特定する.
     *
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    public function resolve(StaffRequest $request): Option;
}

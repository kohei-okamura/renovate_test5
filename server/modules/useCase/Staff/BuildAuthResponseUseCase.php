<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;

/**
 * Auth Response 組み立てユースケース.
 */
interface BuildAuthResponseUseCase
{
    /**
     * StaffからAuth Response を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     * @return array
     */
    public function handle(Context $context, Staff $staff): array;
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;
use Domain\Staff\StaffRememberToken;

/**
 * スタッフリメンバートークン作成ユースケース.
 */
interface CreateStaffRememberTokenUseCase
{
    /**
     * スタッフのリメンバートークンを作成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     * @return \Domain\Staff\StaffRememberToken
     */
    public function handle(Context $context, Staff $staff): StaffRememberToken;
}

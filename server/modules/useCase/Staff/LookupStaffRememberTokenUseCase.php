<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * スタッフリメンバートークン取得ユースケース.
 */
interface LookupStaffRememberTokenUseCase
{
    /**
     * Get the StaffRememberToken.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$id
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$id): Seq;
}

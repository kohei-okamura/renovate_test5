<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;
use ScalikePHP\Option;

/**
 * スタッフ登録ユースケース.
 */
interface CreateStaffUseCase
{
    /**
     * スタッフを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     * @param \Domain\Staff\Invitation[]|\ScalikePHP\Option $invitationOption
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, Staff $staff, Option $invitationOption): void;
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;

/**
 * 招待を用いるスタッフ登録ユースケース.
 */
interface CreateStaffWithInvitationUseCase
{
    /**
     * 招待を用いてスタッフを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $invitationId
     * @param \Domain\Staff\Staff $staff
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, int $invitationId, Staff $staff): void;
}

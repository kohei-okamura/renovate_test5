<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 招待登録ユースケース.
 */
interface CreateInvitationUseCase
{
    /**
     * 招待を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Invitation[]&\ScalikePHP\Seq $invitations
     * @throws \Throwable
     * @return \Domain\Staff\Invitation[]&\ScalikePHP\Seq
     */
    public function handle(Context $context, Seq $invitations): Seq;
}

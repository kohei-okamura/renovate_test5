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
 * 招待取得ユースケース.
 */
interface LookupInvitationUseCase
{
    /**
     * ID を指定して招待を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$id
     * @return \Domain\Staff\Invitation[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$id): Seq;
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 招待取得ユースケース.
 */
interface LookupInvitationByTokenUseCase
{
    /**
     * トークンを指定して招待を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @return \ScalikePHP\Option
     */
    public function handle(Context $context, string $token): Option;
}

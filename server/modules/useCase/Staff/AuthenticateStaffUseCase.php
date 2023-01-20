<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * Authenticate Staff use case.
 */
interface AuthenticateStaffUseCase
{
    /**
     * 認証を行う.
     *
     * @param \Domain\Context\Context $context
     * @param string $email
     * @param string $password
     * @param bool $rememberMe
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    public function handle(Context $context, string $email, string $password, bool $rememberMe): Option;
}

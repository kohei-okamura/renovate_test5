<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\User\User;
use ScalikePHP\Option;

/**
 * 利用者：自治体助成情報特定ユースケース.
 */
interface IdentifyUserDwsSubsidyUseCase
{
    /**
     * 指定した時点で有効な利用者：自治体助成情報を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\User\UserDwsSubsidy[]|\ScalikePHP\Option
     */
    public function handle(Context $context, User $user, Carbon $targetDate): Option;
}

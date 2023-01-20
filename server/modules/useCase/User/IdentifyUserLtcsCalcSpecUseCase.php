<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\User\User;
use ScalikePHP\Option;

/**
 * 介護保険サービス：利用者別算定情報特定ユースケース
 */
interface IdentifyUserLtcsCalcSpecUseCase
{
    /**
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \ScalikePHP\Option
     */
    public function handle(Context $context, User $user, Carbon $targetDate): Option;
}

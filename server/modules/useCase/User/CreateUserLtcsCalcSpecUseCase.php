<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\User\UserLtcsCalcSpec;

/**
 * 介護保険サービス：利用者別算定情報登録ユースケース.
 */
interface CreateUserLtcsCalcSpecUseCase
{
    /**
     * 介護保険サービス：利用者別算定情報を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\User\UserLtcsCalcSpec $calcSpec
     * @return \Domain\User\UserLtcsCalcSpec
     */
    public function handle(Context $context, int $userId, UserLtcsCalcSpec $calcSpec): UserLtcsCalcSpec;
}

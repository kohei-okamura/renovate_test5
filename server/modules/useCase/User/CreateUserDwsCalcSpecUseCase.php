<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\User\UserDwsCalcSpec;

/**
 * 障害福祉サービス：利用者別算定情報登録ユースケース.
 */
interface CreateUserDwsCalcSpecUseCase
{
    /**
     * 障害福祉サービス：利用者別算定情報を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\User\UserDwsCalcSpec $calcSpec
     * @return \Domain\User\UserDwsCalcSpec
     */
    public function handle(Context $context, int $userId, UserDwsCalcSpec $calcSpec): UserDwsCalcSpec;
}

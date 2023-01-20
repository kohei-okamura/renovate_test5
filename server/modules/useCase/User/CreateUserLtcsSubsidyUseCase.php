<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\User\UserLtcsSubsidy;

/**
 * 公費情報登録ユースケース
 */
interface CreateUserLtcsSubsidyUseCase
{
    /**
     * 公費情報を登録する
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\User\UserLtcsSubsidy $userLtcsSubsidy
     * @return \Domain\User\UserLtcsSubsidy
     */
    public function handle(Context $context, int $userId, UserLtcsSubsidy $userLtcsSubsidy): UserLtcsSubsidy;
}

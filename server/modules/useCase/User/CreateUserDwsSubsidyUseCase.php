<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\User\UserDwsSubsidy;

/**
 * 自治体助成情報登録ユースケース
 */
interface CreateUserDwsSubsidyUseCase
{
    /**
     * 自治体助成情報を登録する
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\User\UserDwsSubsidy $userDwsSubsidy
     * @return \Domain\User\UserDwsSubsidy
     */
    public function handle(Context $context, int $userId, UserDwsSubsidy $userDwsSubsidy): UserDwsSubsidy;
}

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
 * 公費情報更新ユースケース
 */
interface EditUserLtcsSubsidyUseCase
{
    /**
     * 公費情報を更新する
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\User\UserLtcsSubsidy
     */
    public function handle(Context $context, int $userId, int $id, array $values): UserLtcsSubsidy;
}

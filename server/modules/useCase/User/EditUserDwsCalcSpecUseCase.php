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
 * 障害福祉サービス：利用者別算定情報編集ユースケース.
 */
interface EditUserDwsCalcSpecUseCase
{
    /**
     * 障害福祉サービス：利用者別算定情報を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\User\UserDwsCalcSpec
     */
    public function handle(Context $context, int $userId, int $id, array $values): UserDwsCalcSpec;
}

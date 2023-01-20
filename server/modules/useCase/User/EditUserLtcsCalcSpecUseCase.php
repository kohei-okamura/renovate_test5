<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\User\UserLtcsCalcSpec;

/**
 * 介護保険サービス：利用者別算定情報編集ユースケース.
 */
interface EditUserLtcsCalcSpecUseCase
{
    /**
     * 介護保険サービス：利用者別算定情報を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\User\UserLtcsCalcSpec
     */
    public function handle(Context $context, int $userId, int $id, array $values): UserLtcsCalcSpec;
}

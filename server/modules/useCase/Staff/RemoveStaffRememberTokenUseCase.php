<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;

/**
 * スタッフリメンバートークン削除ユースケース.
 */
interface RemoveStaffRememberTokenUseCase
{
    /**
     * スタッフのリメンバートークンを作成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return void
     */
    public function handle(Context $context, int $id): void;
}

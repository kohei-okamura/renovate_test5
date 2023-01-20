<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;

/**
 * ロール削除ユースケース.
 */
interface DeleteRoleUseCase
{
    /**
     * ロールを削除する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     */
    public function handle(Context $context, int $id): void;
}

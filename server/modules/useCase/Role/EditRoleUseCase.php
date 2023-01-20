<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\Role\Role;

/**
 * ロール編集ユースケース.
 */
interface EditRoleUseCase
{
    /**
     * ロールを編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return \Domain\Role|Role
     */
    public function handle(Context $context, int $id, array $values): Role;
}

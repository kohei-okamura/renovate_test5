<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 利用者選択肢一覧取得ユースケース.
 */
interface GetIndexUserOptionUseCase
{
    /**
     * 利用者選択肢を一覧取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array|int[] $officeIds
     * @return \ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, array $officeIds): Seq;
}

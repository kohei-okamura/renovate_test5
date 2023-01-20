<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\OwnExpenseProgram;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 自費サービス情報取得ユースケース.
 */
interface LookupOwnExpenseProgramUseCase
{
    /**
     * ID を指定して自費サービス情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$id
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$id): Seq;
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\OwnExpenseProgram;

use Domain\Context\Context;
use Domain\OwnExpenseProgram\OwnExpenseProgram;

/**
 * 自費サービス情報登録ユースケース.
 */
interface CreateOwnExpenseProgramUseCase
{
    /**
     * 自費サービス情報を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram $ownExpenseProgram
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram
     */
    public function handle(Context $context, OwnExpenseProgram $ownExpenseProgram): OwnExpenseProgram;
}

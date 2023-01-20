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
 * 自費サービス情報編集ユースケース.
 */
interface EditOwnExpenseProgramUseCase
{
    /**
     * 自費サービス情報を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram
     */
    public function handle(Context $context, int $id, array $values): OwnExpenseProgram;
}

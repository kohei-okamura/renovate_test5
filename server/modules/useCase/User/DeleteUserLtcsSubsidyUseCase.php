<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;

/**
 * 公費情報削除ユースケース.
 */
interface DeleteUserLtcsSubsidyUseCase
{
    /**
     * 公費情報を削除する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @return void
     */
    public function handle(Context $context, int $userId, int $id): void;
}

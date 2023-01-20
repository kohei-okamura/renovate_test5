<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Permission\Permission;

/**
 * 契約編集ユースケース.
 */
interface EditContractUseCase
{
    /**
     * 契約を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $userId
     * @param int $id
     * @param array $values
     * @throws \Throwable
     * @return \Domain\Contract\Contract
     */
    public function handle(Context $context, Permission $permission, int $userId, int $id, array $values): Contract;
}

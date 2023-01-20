<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Context\Context;
use Domain\Contract\Contract;

/**
 * 契約登録ユースケース.
 */
interface CreateContractUseCase
{
    /**
     * 契約を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Contract\Contract $contract
     * @return \Domain\Contract\Contract
     */
    public function handle(Context $context, int $userId, Contract $contract): Contract;
}

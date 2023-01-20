<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Option;

/**
 * 契約特定ユースケース.
 *
 * 事業所や利用者などから契約を特定する
 */
interface IdentifyContractUseCase
{
    /**
     * 契約を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\ServiceSegment $serviceSegment
     * @param \Domain\Common\Carbon $targetDate 対象日
     * @return \ScalikePHP\Option
     */
    public function handle(
        Context $context,
        Permission $permission,
        int $officeId,
        int $userId,
        ServiceSegment $serviceSegment,
        Carbon $targetDate
    ): Option;
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Option;

/**
 * 介護保険サービス：予実取得ユースケース.
 */
interface GetLtcsProvisionReportUseCase
{
    /**
     * 介護保険サービス：予実取得.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]|\ScalikePHP\Option
     */
    public function handle(
        Context $context,
        Permission $permission,
        int $officeId,
        int $userId,
        Carbon $providedIn
    ): Option;
}

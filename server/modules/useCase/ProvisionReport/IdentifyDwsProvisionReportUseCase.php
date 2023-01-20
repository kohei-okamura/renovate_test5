<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Option;

/**
 * 障害福祉サービス：予実特定ユースケース.
 */
interface IdentifyDwsProvisionReportUseCase
{
    /**
     * 障害福祉サービス：予実を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option
     */
    public function handle(
        Context $context,
        Permission $permission,
        int $officeId,
        int $userId,
        Carbon $providedIn
    ): Option;
}

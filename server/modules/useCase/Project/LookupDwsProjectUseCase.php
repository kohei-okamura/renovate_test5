<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：計画取得ユースケース.
 */
interface LookupDwsProjectUseCase
{
    /**
     * ID を指定して障害福祉サービス：計画を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $userId
     * @param int ...$ids
     * @return \Domain\Project\DwsProject[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq;
}

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
 * 介護保険サービス計画取得ユースケース.
 */
interface LookupLtcsProjectUseCase
{
    /**
     * ID を指定して介護保険サービス計画を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $userId
     * @param int ...$ids
     * @return \Domain\Project\LtcsProject[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq;
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 重複契約取得ユースケース.
 */
interface GetOverlapContractUseCase
{
    /**
     * 重複契約している契約の一覧を取得する.
     *
     * 利用者、事業所、事業領域が同一かつ状態が仮契約 or 本契約のものを返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $userId 利用者ID
     * @param int $officeId 事業所ID
     * @param \Domain\Common\ServiceSegment $serviceSegment 事業領域
     * @return \ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Permission $permission,
        int $userId,
        int $officeId,
        ServiceSegment $serviceSegment,
    ): Seq;
}

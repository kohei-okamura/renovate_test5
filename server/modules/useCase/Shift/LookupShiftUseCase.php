<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

interface LookupShiftUseCase
{
    /**
     * ID を指定して勤務シフトを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$ids
     * @return \Domain\Shift\Shift[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq;
}

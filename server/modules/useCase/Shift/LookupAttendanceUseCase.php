<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

interface LookupAttendanceUseCase
{
    /**
     * ID を指定して勤務実績を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$id
     * @return \Domain\Shift\Attendance[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$id): Seq;
}

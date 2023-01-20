<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Shift\Attendance;

/**
 * 勤務実績登録インターフェース.
 */
interface CreateAttendanceUseCase
{
    /**
     * 勤務実績を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Shift\Attendance $attendance
     * @throws \Throwable
     * @return \Domain\Shift\Attendance
     */
    public function handle(Context $context, Attendance $attendance): Attendance;
}

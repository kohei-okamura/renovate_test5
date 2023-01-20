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
 * 勤務実績編集ユースケース.
 */
interface EditAttendanceUseCase
{
    /**
     * 勤務実績を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @throws \Throwable
     * @return \Domain\Shift\Attendance
     */
    public function handle(Context $context, int $id, array $values): Attendance;
}

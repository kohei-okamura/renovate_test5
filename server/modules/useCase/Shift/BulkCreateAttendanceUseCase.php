<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\Carbon;

/**
 * 勤務実績一括登録ユースケース.
 */
interface BulkCreateAttendanceUseCase
{
    /**
     * 勤務実績を勤務シフトから仮登録する.
     *
     * @param \Domain\Common\Carbon $targetDate 仮登録対象の日付
     * @param int[] $organizationIds 事業者ID
     * @throws \Throwable
     * @return int
     */
    public function handle(Carbon $targetDate, int ...$organizationIds): int;
}

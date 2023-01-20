<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;

/**
 * 勤務実績キャンセルユースケース.
 */
interface CancelAttendanceUseCase
{
    /**
     * 勤務実績をキャンセルする.
     *
     * @param \Domain\Context\Context $context
     * @param string $reason
     * @param int[] $ids
     * @return void
     */
    public function handle(Context $context, string $reason, int ...$ids): void;
}

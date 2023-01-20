<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Shift\Shift;

/**
 * 勤務シフト登録インターフェース.
 *
 * Interface CreateShiftUseCase
 */
interface CreateShiftUseCase
{
    /**
     * 勤務シフトを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Shift\Shift $shift
     * @throws \Throwable
     * @return \Domain\Shift\Shift
     */
    public function handle(Context $context, Shift $shift): Shift;
}

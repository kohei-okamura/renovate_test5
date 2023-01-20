<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;

/**
 * 勤務シフト確定ユースケース.
 */
interface ConfirmShiftUseCase
{
    /**
     * 勤務シフトを確定する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$ids
     * @return void
     */
    public function handle(Context $context, int ...$ids): void;
}

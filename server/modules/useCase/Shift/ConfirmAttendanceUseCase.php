<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;

/**
 * 勤務実績一括確定ユースケース
 */
interface ConfirmAttendanceUseCase
{
    /**
     * 勤務実績を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $id
     */
    public function handle(Context $context, int ...$id): void;
}

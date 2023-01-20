<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Context\Context;

/**
 * スタッフ出勤確認承認ユースケース.
 */
interface AcknowledgeStaffAttendanceUseCase
{
    /**
     * スタッフ出勤確認を承認する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     */
    public function handle(Context $context, string $token): void;
}

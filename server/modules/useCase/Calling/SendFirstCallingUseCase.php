<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Common\CarbonRange;
use Domain\Context\Context;

/**
 * 出勤確認第一通知ユースケース.
 */
interface SendFirstCallingUseCase
{
    /**
     * 出勤確認通知から第一通知を行う.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\CarbonRange $range
     */
    public function handle(Context $context, CarbonRange $range): void;
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * スタッフ出勤勤務シフト取得ユースケース.
 */
interface GetShiftsByTokenUseCase
{
    /**
     * スタッフ出勤から勤務シフトを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @return \Domain\FinderResult|\Domain\Shift\Shift[]
     */
    public function handle(Context $context, string $token): FinderResult;
}

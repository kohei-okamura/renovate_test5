<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Common\Range;
use Domain\Context\Context;

/**
 * 出勤確認作成ユースケース.
 */
interface CreateCallingsUseCase
{
    /**
     * 出勤確認作成処理.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Range $datetimeRange
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, Range $datetimeRange): void;
}

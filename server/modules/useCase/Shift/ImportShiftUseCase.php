<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;

/**
 * 勤務シフト一括登録ユースケース.
 */
interface ImportShiftUseCase
{
    /**
     * 勤務シフトを一括登録する.
     *
     * @param \Domain\Context\Context $context
     * @param string $path
     * @throws \Lib\Exceptions\ValidationException
     * @throws \Lib\Exceptions\PhpSpreadsheetException
     * @return void
     */
    public function handle(Context $context, string $path): void;
}

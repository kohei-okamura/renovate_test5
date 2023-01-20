<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use ScalikePHP\Seq;

/**
 * 勤務シフト読み込みユースケース.
 */
interface LoadShiftUseCase
{
    /**
     * 勤務シフト雛形から勤務シフトデータを読み込む.
     *
     * @param \Domain\Context\Context $context
     * @param Worksheet $worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return \Domain\Shift\Shift[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Worksheet $worksheet): Seq;
}

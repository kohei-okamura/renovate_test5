<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\CarbonRange;
use Domain\Context\Context;

/**
 * 勤務シフト一括登録雛形ファイル生成ユースケース.
 */
interface GenerateShiftTemplateUseCase
{
    /**
     * 勤務シフト一括登録雛形ファイルを生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\CarbonRange $range 生成期間
     * @param bool $isCopy Shiftのデータをコピーして生成する
     * @param array $filterParams
     * @return string
     */
    public function handle(Context $context, CarbonRange $range, bool $isCopy, array $filterParams): string;
}

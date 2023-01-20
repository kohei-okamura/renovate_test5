<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\UserBilling\ZenginRecord;
use SplFileInfo;

/**
 * 全銀フォーマットファイルパースユースケース.
 */
interface ParseZenginFormatUseCase
{
    /**
     * 全銀フォーマットのファイルをパースする.
     *
     * @param \Domain\Context\Context $context
     * @param \SplFileInfo $file
     * @return mixed
     */
    public function handle(Context $context, SplFileInfo $file): ZenginRecord;
}

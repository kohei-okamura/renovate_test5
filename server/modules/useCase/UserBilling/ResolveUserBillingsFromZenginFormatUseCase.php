<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use ScalikePHP\Map;
use SplFileInfo;

/**
 * 利用者請求導出ユースケース.
 */
interface ResolveUserBillingsFromZenginFormatUseCase
{
    /**
     * 全銀ファイルから利用者請求を導出する.
     *
     * @param \Domain\Context\Context $context
     * @param \SplFileInfo $file
     * @return \ScalikePHP\Map key=利用者請求ID value=[利用者請求：振替結果コード, 引落日]
     */
    public function handle(Context $context, SplFileInfo $file): Map;
}

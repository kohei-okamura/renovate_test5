<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;

/**
 * 全銀ファイルアップロードユースケース.
 */
interface ImportWithdrawalTransactionFileUseCase
{
    /**
     * 全銀ファイルをアップロードする.
     *
     * @param \Domain\Context\Context $context
     * @param string $path
     * @return void
     */
    public function handle(Context $context, string $path): void;
}

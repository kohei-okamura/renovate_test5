<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;

/**
 * 全銀ファイル作成ユースケース.
 */
interface GenerateWithdrawalTransactionFileUseCase
{
    /**
     * 全銀ファイルを作成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return string
     */
    public function handle(Context $context, int $id): string;
}

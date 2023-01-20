<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;

/**
 * 利用者請求：介護サービス利用明細書 PDF 生成ユースケース.
 */
interface GenerateUserBillingStatementPdfUseCase
{
    /**
     * 利用者請求：介護サービス利用明細書 PDF を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param array $ids 利用者請求 ID
     * @param \Domain\Common\Carbon $issuedOn
     * @return string
     */
    public function handle(Context $context, array $ids, Carbon $issuedOn): string;
}

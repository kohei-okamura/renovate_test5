<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 利用者請求：請求書 PDFパラメータ組み立てユースケース.
 */
interface BuildUserBillingInvoicePdfParamUseCase
{
    /**
     * 利用者請求：請求書 PDFのパラメータを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\UserBilling\UserBilling[] $userBillings
     * @param \Domain\Common\Carbon $issuedOn
     * @return array
     */
    public function handle(Context $context, Seq $userBillings, Carbon $issuedOn): array;
}

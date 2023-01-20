<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingFile;
use Domain\Context\Context;

/**
 * 介護保険サービス：介護給付費請求書・明細書 PDF 生成ユースケース.
 */
interface CreateLtcsBillingInvoicePdfUseCase
{
    /**
     * 介護保険サービス：介護給付費請求書・明細書 PDF を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @throws \Lib\Exceptions\NotFoundException
     * @throws \Lib\Exceptions\TemporaryFileAccessException
     * @return \Domain\Billing\LtcsBillingFile
     */
    public function handle(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): LtcsBillingFile;
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 *  利用者負担額一覧表 PDF 生成ユースケース.
 */
interface GenerateCopayListPdfUseCase
{
    /**
     *  利用者負担額一覧表 PDF を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return string
     */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles, Seq $statements): string;
}

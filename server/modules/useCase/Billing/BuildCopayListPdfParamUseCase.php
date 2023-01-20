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
 * 利用者負担額一覧表 PDFパラメータ組み立てユースケース.
 */
interface BuildCopayListPdfParamUseCase
{
    /**
     * 利用者負担額一覧表 PDFパラメータを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @param \Domain\Billing\DwsBillingStatement&\ScalikePHP\Seq $statements
     * @return array
     */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles, Seq $statements): array;
}

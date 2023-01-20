<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票PDFのパラメータを組み立てユースケース.
 *
 * FYI: 物理名が「居宅介護向け」に見えるけど重度訪問介護についてもここで扱う.
 * TODO: 物理名を見直す.
 */
interface BuildDwsHomeHelpServiceServiceReportPdfParamUseCase
{
    /**
     * 障害福祉サービス：サービス提供実績記録票PDFのパラメータを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return \Domain\Billing\DwsBillingServiceReportPdf[]&\ScalikePHP\Seq
     */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): Seq;
}

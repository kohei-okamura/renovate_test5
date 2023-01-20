<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求算定元データ一覧組み立てユースケース.
 */
interface BuildDwsBillingSourceListUseCase
{
    /**
     * 障害福祉サービス：請求算定元データの一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Seq $provisionReports
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Seq $previousProvisionReports
     * @return \Domain\Billing\DwsBillingSource[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Seq $provisionReports, Seq $previousProvisionReports): Seq;
}

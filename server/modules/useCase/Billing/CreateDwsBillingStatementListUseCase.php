<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Context\Context;
use Domain\Office\Office;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書一覧生成ユースケース.
 */
interface CreateDwsBillingStatementListUseCase
{
    /**
     *障害福祉サービス：明細書の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Office $office, DwsBillingBundle $bundle): Seq;
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書：契約一覧組み立てユースケース.
 */
interface BuildDwsBillingStatementContractListUseCase
{
    /**
     * 障害福祉サービス：明細書：契約の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\DwsBillingStatementContract[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Office $office, DwsCertification $certification, Carbon $providedIn): Seq;
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use ScalikePHP\Option;

/**
 * 利用者負担上限額管理結果票リフレッシュユースケース.
 */
interface RefreshDwsBillingCopayCoordinationUseCase
{
    /**
     * 利用者負担上限額管理結果票をリフレッシュする.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param \Domain\DwsCertification\CopayCoordination[]|\ScalikePHP\Option $copayCoordination リフレッシュ対象の利用者負担上限額管理結果票
     * @param \Domain\DwsCertification\DwsCertification $dwsCertification
     * @param \Domain\Office\Office $office
     * @throws \Throwable
     * @return void
     */
    public function handle(
        Context $context,
        DwsBillingStatement $statement,
        Option $copayCoordination,
        DwsCertification $dwsCertification,
        Office $office
    ): void;
}

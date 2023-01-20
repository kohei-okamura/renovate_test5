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
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：サービス詳細一覧組み立てユースケース（居宅介護用）.
 */
interface BuildDwsHomeHelpServiceServiceDetailListUseCase
{
    /**
     * 障害福祉サービス請求：サービス詳細（居宅介護用）の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Office\HomeHelpServiceCalcSpec[]&\ScalikePHP\Option $specOption
     * @param \Domain\User\UserDwsCalcSpec[]&\ScalikePHP\Option $userSpec
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option $previousReport
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Carbon $providedIn,
        Option $specOption,
        Option $userSpec,
        DwsCertification $certification,
        DwsProvisionReport $provisionReport,
        Option $previousReport
    ): Seq;
}

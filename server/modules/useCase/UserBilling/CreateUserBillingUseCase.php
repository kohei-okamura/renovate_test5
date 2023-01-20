<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\User\User;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Option;

/**
 * 利用者請求生成ユースケース.
 */
interface CreateUserBillingUseCase
{
    /**
     * 利用者請求を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Option $dwsBillingStatement
     * @param \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Option $ltcsBillingStatement
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Option $dwsProvisionReport
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]|\ScalikePHP\Option $ltcsProvisionReport
     * @throws \Throwable
     * @return \Domain\UserBilling\UserBilling
     */
    public function handle(
        Context $context,
        User $user,
        Office $office,
        Carbon $providedIn,
        Option $dwsBillingStatement,
        Option $ltcsBillingStatement,
        Option $dwsProvisionReport,
        Option $ltcsProvisionReport
    ): UserBilling;
}

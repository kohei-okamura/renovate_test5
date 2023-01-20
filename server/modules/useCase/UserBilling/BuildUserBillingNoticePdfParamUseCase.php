<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 代理受領額通知書パラメータ組み立てユースケース.
 */
interface BuildUserBillingNoticePdfParamUseCase
{
    /**
     * 代理受領額通知書のパラメータを組みたてる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\UserBilling\UserBilling[]&\ScalikePHP\Seq $userBillings
     * @param \Domain\Common\Carbon $issuedOn
     * @return array
     */
    public function handle(Context $context, Seq $userBillings, Carbon $issuedOn): array;
}

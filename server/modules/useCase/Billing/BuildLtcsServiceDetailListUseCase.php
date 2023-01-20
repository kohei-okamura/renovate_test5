<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求：サービス詳細一覧組み立てユースケース.
 */
interface BuildLtcsServiceDetailListUseCase
{
    /**
     * 介護保険サービス：請求：サービス詳細の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq $reports
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param bool $usePlan
     * @throws \Lib\Exceptions\NotFoundException
     * @return array|\Domain\Billing\LtcsBillingServiceDetail[]
     */
    public function handle(Context $context, Carbon $providedIn, Seq $reports, Seq $users, bool $usePlan = false): array;
}

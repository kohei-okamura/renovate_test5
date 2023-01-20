<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\User\User;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票生成ユースケース.
 */
interface BuildDwsBillingServiceReportListByIdUseCase
{
    /**
     * サービス提供実績記録票を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option $previousProvisionReport
     * @param \Domain\User\User $user
     * @param bool $isPreview プレビュー用。（プレビューのときは予定のみでも出力する）
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        DwsProvisionReport $provisionReport,
        Option $previousProvisionReport,
        User $user,
        bool $isPreview
    ): Seq;
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use ScalikePHP\Seq;

/**
 * 生活機能向上連携加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase
{
    /**
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \ScalikePHP\Seq $dictionaryEntries
     * @return \ScalikePHP\Seq
     */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries): Seq;
}

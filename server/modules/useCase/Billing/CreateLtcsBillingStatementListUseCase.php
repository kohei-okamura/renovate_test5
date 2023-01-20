<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Context\Context;
use Domain\Office\Office;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書一覧生成ユースケース.
 */
interface CreateLtcsBillingStatementListUseCase
{
    /**
     * 介護保険サービス：請求単位を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]|\ScalikePHP\Seq $reports
     * @throws \Lib\Exceptions\NotFoundException
     * @throws \Throwable
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Office $office, LtcsBillingBundle $bundle, Seq $reports): Seq;
}

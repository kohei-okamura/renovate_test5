<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票簡易取得ユースケース.
 */
interface SimpleLookupDwsBillingServiceReportUseCase
{
    /**
     * ID を指定してサービス提供実績記録票を取得する.
     *
     * 基本的には LookupDwsBillingServiceReportUseCase を使用すること.
     * サービス提供実績記録票IDしかない場合のみこのユースケースを利用すること.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$ids
     * @return \Domain\Billing\DwsBillingServiceReport[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Permission $permission,
        int ...$ids
    ): Seq;
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;

/**
 * 介護保険サービス：予実削除ユースケース.
 */
interface DeleteLtcsProvisionReportUseCase
{
    /**
     * 介護保険サービス：予実を削除する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return void
     */
    public function handle(Context $context, int $officeId, int $userId, Carbon $providedIn): void;
}

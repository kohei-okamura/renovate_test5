<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;

/**
 * 利用者請求一覧生成ユースケース.
 */
interface CreateUserBillingListUseCase
{
    /**
     * 利用者請求一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, Carbon $providedIn): void;
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Option;

/**
 * 事業者別設定取得ユースケース.
 */
interface LookupOrganizationSettingUseCase
{
    /**
     * Contextの事業者情報から事業者別設定を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @return \Domain\Organization\OrganizationSetting[]|\ScalikePHP\Option
     */
    public function handle(Context $context, Permission $permission): Option;
}

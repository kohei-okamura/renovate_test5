<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use Domain\Context\Context;
use Domain\Organization\OrganizationSetting;

/**
 * 事業者別設定登録ユースケース
 */
interface CreateOrganizationSettingUseCase
{
    /**
     * 事業者別設定を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Organization\OrganizationSetting $organizationSetting
     * @return \Domain\Organization\OrganizationSetting
     */
    public function handle(Context $context, OrganizationSetting $organizationSetting): OrganizationSetting;
}

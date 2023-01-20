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
 * 事業者別設定編集ユースケース.
 */
interface EditOrganizationSettingUseCase
{
    /**
     * 事業者別設定を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param array $values
     * @return \Domain\Organization\OrganizationSetting
     */
    public function handle(Context $context, array $values): OrganizationSetting;
}

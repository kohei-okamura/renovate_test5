<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\OfficeGroup;

/**
 * 事業所グループ登録ユースケース.
 */
interface CreateOfficeGroupUseCase
{
    /**
     * 事業所グループを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\OfficeGroup $officeGroup
     * @return \Domain\Office\OfficeGroup
     */
    public function handle(Context $context, OfficeGroup $officeGroup): OfficeGroup;
}

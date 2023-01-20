<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 権限コード一覧取得ユースケース.
 */
interface AggregatePermissionCodeListUseCase
{
    /**
     * 権限コード一覧を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Role\Role[]|\ScalikePHP\Seq $roles
     * @return array|\Domain\Permission\Permission[]
     */
    public function handle(Context $context, Seq $roles): array;
}

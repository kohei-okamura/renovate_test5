<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 *ロール情報取得ユースケース.
 */
interface LookupRoleUseCase
{
    /**
     * ID を指定して ロール情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$id
     * @return \Domain\Role\Role[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$id): Seq;
}

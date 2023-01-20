<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase;

use Domain\Context\Context;

/**
 * エンティティが存在するかを検査する.
 */
interface EntityExists
{
    /**
     * 指定したIDのエンティティが存在するか.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return bool
     */
    public function handle(Context $context, int $id): bool;
}

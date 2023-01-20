<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;

/**
 * スタッフ編集ユースケース.
 */
interface EditStaffUseCase
{
    /**
     * スタッフを編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return array
     */
    public function handle(Context $context, int $id, array $values): array;
}

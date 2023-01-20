<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;

/**
 * スタッフ情報取得ユースケース.
 */
interface GetStaffInfoUseCase
{
    /**
     * スタッフ情情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return array
     */
    public function handle(Context $context, int $id): array;
}

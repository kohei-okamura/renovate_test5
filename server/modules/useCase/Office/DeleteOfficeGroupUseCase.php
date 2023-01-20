<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;

/**
 * 事業所グループ削除ユースケース.
 */
interface DeleteOfficeGroupUseCase
{
    /**
     * 事業所グループを削除する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return void
     */
    public function handle(Context $context, int $id): void;
}

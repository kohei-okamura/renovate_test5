<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 事業所グループ選択肢一覧取得ユースケース.
 */
interface GetIndexOfficeGroupOptionUseCase
{
    /**
     * 事業所グループ選択肢を一覧取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @return \ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission): Seq;
}

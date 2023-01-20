<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;

/**
 * 事業所グループ一括編集ユースケース.
 */
interface BulkEditOfficeGroupUseCase
{
    /**
     * 事業所グループを一括編集する.
     *
     * @param \Domain\Context\Context $context
     * @param array $requestList
     * @return void
     */
    public function handle(Context $context, array $requestList): void;
}

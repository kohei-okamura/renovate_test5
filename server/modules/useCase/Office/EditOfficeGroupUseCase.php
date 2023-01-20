<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * 事業所グループ編集ユースケース.
 */
interface EditOfficeGroupUseCase
{
    /**
     * 事業所グループを編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, int $id, array $values): FinderResult;
}

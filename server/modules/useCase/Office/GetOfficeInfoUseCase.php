<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;

/**
 * 事業所情報取得ユースケース.
 */
interface GetOfficeInfoUseCase
{
    /**
     * 事業所情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return array
     */
    public function handle(Context $context, int $id): array;
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 事業所グループ情報取得ユースケース.
 */
interface LookupOfficeGroupUseCase
{
    /**
     * ID を指定して 事業所グループ情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$id
     * @return \Domain\Office\OfficeGroup[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$id): Seq;
}

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
 * 事業所取得ユースケース.
 */
interface LookupOfficeUseCase
{
    /**
     * ID を指定して事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param int ...$id
     * @return \Domain\Office\Office[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, array $permissions, int ...$id): Seq;
}

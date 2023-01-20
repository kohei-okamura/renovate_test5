<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 事業所算定情報（障害・居宅介護）取得ユースケース.
 */
interface LookupHomeHelpServiceCalcSpecUseCase
{
    /**
     * ID を指定して事業所算定情報（障害・居宅介護）を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param int $officeId
     * @param int[] $ids
     * @return \Domain\Office\HomeHelpServiceCalcSpec[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, array $permissions, int $officeId, int ...$ids): Seq;
}

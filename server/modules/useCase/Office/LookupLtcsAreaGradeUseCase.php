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
 * 介保地域区分取得ユースケース.
 */
interface LookupLtcsAreaGradeUseCase
{
    /**
     * IDを指定して介保地域区分を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$ids
     * @return \Domain\LtcsAreaGrade\LtcsAreaGrade[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$ids): Seq;
}

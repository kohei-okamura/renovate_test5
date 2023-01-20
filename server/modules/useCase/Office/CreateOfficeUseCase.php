<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\Office;

/**
 * 事業所登録ユースケース.
 */
interface CreateOfficeUseCase
{
    /**
     * 事業所を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param callable $f
     * @return \Domain\Office\Office
     */
    public function handle(Context $context, Office $office, callable $f): Office;
}

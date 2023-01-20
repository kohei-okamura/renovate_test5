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
 * 事業所編集ユースケース.
 */
interface EditOfficeUseCase
{
    /**
     * 事業所を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @param callable $f
     * @return \Domain\Office\Office
     */
    public function handle(Context $context, int $id, array $values, callable $f): Office;
}

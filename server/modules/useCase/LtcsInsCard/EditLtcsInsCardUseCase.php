<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCard;

/**
 * 介護保険被保険者証編集ユースケース.
 */
interface EditLtcsInsCardUseCase
{
    /**
     * 介護保険被保険者証を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\LtcsInsCard\LtcsInsCard
     */
    public function handle(Context $context, int $userId, int $id, array $values): LtcsInsCard;
}

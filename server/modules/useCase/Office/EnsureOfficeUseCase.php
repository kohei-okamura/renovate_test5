<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;

/**
 * 事業所保証ユースケース.
 */
interface EnsureOfficeUseCase
{
    /**
     * officeIdを指定して事業所の保証を行う.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param int $officeId 事業所ID
     */
    public function handle(Context $context, array $permissions, int $officeId): void;
}

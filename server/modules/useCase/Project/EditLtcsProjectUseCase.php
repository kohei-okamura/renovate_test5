<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Project\LtcsProject;

/**
 * 介護保険サービス：計画編集ユースケース.
 */
interface EditLtcsProjectUseCase
{
    /**
     * 介護保険サービス：計画を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\Project\LtcsProject
     */
    public function handle(Context $context, int $userId, int $id, array $values): LtcsProject;
}

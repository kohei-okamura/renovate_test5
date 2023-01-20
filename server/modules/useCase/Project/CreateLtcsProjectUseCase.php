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
 * 介護保険サービス：計画登録ユースケース.
 */
interface CreateLtcsProjectUseCase
{
    /**
     * 介護保険サービス：計画を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Project\LtcsProject $ltcsProject
     * @return \Domain\Project\LtcsProject
     */
    public function handle(Context $context, int $userId, LtcsProject $ltcsProject): LtcsProject;
}

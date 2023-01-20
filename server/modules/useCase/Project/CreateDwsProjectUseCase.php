<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Project\DwsProject;

/**
 * 障害福祉サービス：計画登録ユースケース.
 */
interface CreateDwsProjectUseCase
{
    /**
     * 障害福祉サービス：計画を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Project\DwsProject $dwsProject
     * @return \Domain\Project\DwsProject
     */
    public function handle(Context $context, int $userId, DwsProject $dwsProject): DwsProject;
}

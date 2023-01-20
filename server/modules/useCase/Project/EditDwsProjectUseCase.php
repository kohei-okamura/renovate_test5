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
 * 障害福祉サービス計画編集ユースケース.
 */
interface EditDwsProjectUseCase
{
    /**
     * 障害福祉サービス計画を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\Project\DwsProject
     */
    public function handle(Context $context, int $userId, int $id, array $values): DwsProject;
}

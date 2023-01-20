<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 勤務シフト一括確定ジョブ実行ユースケース.
 */
interface RunConfirmShiftJobUseCase
{
    /**
     * 勤務シフト一括確定ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int[] $ids
     * @return void
     */
    public function handle(Context $context, DomainJob $domainJob, array $ids): void;
}

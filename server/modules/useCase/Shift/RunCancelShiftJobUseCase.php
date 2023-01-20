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
 * 勤務シフトキャンセルジョブ実行ユースケース.
 */
interface RunCancelShiftJobUseCase
{
    /**
     * 勤務シフトキャンセル生成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param string $reason
     * @param int[] $ids
     * @return void
     */
    public function handle(Context $context, DomainJob $domainJob, string $reason, int ...$ids): void;
}

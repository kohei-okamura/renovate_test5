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
 * 勤務シフト一括登録ジョブ実行ユースケース.
 */
interface RunImportShiftJobUseCase
{
    /**
     * 勤務シフト一括登録ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param string $path
     * @param \Domain\Job\Job $domainJob
     * @return void
     */
    public function handle(Context $context, string $path, DomainJob $domainJob): void;
}

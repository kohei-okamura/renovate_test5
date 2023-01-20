<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\Job\Job as DomainJob;

/**
 * ファイルアップロードジョブ登録ユースケース.
 */
interface CreateJobWithFileUseCase
{
    /**
     * ジョブを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\File\FileInputStream $stream
     * @param callable $f
     * @return \Domain\Job\Job
     */
    public function handle(Context $context, FileInputStream $stream, callable $f): DomainJob;
}

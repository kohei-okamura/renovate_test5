<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Job Fixture
 * @mixin ExamplesConsumer
 */
trait JobFixture
{
    /**
     * 非同期ジョブ 登録.
     *
     * @return void
     */
    protected function createJobs(): void
    {
        foreach ($this->examples->jobs as $entity) {
            Job::fromDomain($entity)->saveIfNotExists();
        }
    }
}

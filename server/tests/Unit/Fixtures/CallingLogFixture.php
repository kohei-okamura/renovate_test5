<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Calling\CallingLog;

/**
 * CallingLog fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait CallingLogFixture
{
    /**
     * 出勤確認送信履歴 登録.
     *
     * @return void
     */
    protected function createCallingLogs(): void
    {
        foreach ($this->examples->callingLogs as $entity) {
            CallingLog::fromDomain($entity)->saveIfNotExists();
        }
    }
}

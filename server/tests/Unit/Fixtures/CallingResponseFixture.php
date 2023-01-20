<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Calling\CallingResponse;

/**
 * CallingResponse fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait CallingResponseFixture
{
    /**
     * 出勤確認応答 登録.
     *
     * @return void
     */
    protected function createCallingResponses(): void
    {
        foreach ($this->examples->callingResponses as $entity) {
            CallingResponse::fromDomain($entity)->saveIfNotExists();
        }
    }
}

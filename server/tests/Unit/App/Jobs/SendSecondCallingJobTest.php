<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\SendSecondCallingJob;
use Domain\Common\CarbonRange;
use Domain\Common\Range;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\SendSecondCallingUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\SendSecondCallingJob} Test.
 */
final class SendSecondCallingJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use SendSecondCallingUseCaseMixin;
    use UnitSupport;

    private SendSecondCallingJob $job;

    private Range $range;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendSecondCallingJobTest $self): void {
            $self->range = CarbonRange::create();

            $self->job = new SendSecondCallingJob($self->context, $self->range);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use SendSecondCallingUseCase', function () {
            $this->sendSecondCallingUseCase
                ->expects('handle')
                ->with($this->context, $this->range)
                ->andReturnNull();

            $this->job->handle($this->sendSecondCallingUseCase);
        });
    }
}

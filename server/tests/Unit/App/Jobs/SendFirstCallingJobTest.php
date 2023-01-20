<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\SendFirstCallingJob;
use Domain\Common\CarbonRange;
use Domain\Common\Range;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\SendFirstCallingUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\SendFirstCallingJob} Test.
 */
final class SendFirstCallingJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use SendFirstCallingUseCaseMixin;
    use UnitSupport;

    private SendFirstCallingJob $job;

    private Range $range;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendFirstCallingJobTest $self): void {
            $self->range = CarbonRange::create();

            $self->job = new SendFirstCallingJob($self->context, $self->range);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use UseCase', function () {
            $this->sendFirstCallingUseCase
                ->expects('handle')
                ->with($this->context, $this->range)
                ->andReturnNull();

            $this->job->handle($this->sendFirstCallingUseCase);
        });
    }
}

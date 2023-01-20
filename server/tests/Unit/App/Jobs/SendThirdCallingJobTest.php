<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\SendThirdCallingJob;
use Domain\Common\CarbonRange;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SendThirdCallingUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\SendThirdCallingJob} Test.
 */
final class SendThirdCallingJobTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use SendThirdCallingUseCaseMixin;
    use UnitSupport;

    private SendThirdCallingJob $job;
    private CarbonRange $range;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendThirdCallingJobTest $self): void {
            $self->range = CarbonRange::create();
            $self->job = new SendThirdCallingJob($self->context, $self->range);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use SendThirdCallingUseCase', function (): void {
            $this->sendThirdCallingUseCase
                ->expects('handle')
                ->with($this->context, $this->range)
                ->andReturnNull();

            $this->job->handle($this->sendThirdCallingUseCase);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\ConfirmShiftJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunConfirmShiftJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * ConfirmShiftJob のテスト.
 */
final class ConfirmShiftJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunConfirmShiftJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private ConfirmShiftJob $job;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfirmShiftJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->job = new ConfirmShiftJob($self->context, $self->domainJob, [$self->examples->shifts[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunConfirmShiftJobUseCase', function (): void {
            $this->runConfirmShiftJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, [$this->examples->shifts[0]->id])
                ->andReturnNull();

            $this->job->handle($this->runConfirmShiftJobUseCase);
        });
    }
}

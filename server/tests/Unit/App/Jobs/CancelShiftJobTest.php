<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CancelShiftJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCancelShiftJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * CancelShiftJob のテスト.
 */
final class CancelShiftJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCancelShiftJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private CancelShiftJob $job;
    private string $reason = 'キャンセル理由';

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelShiftJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->job = new CancelShiftJob($self->context, $self->domainJob, $self->reason, $self->examples->shifts[0]->id);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCancelShiftJobUseCase', function (): void {
            $this->runCancelShiftJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->reason, $this->examples->shifts[0]->id)
                ->andReturnNull();

            $this->job->handle($this->runCancelShiftJobUseCase);
        });
    }
}

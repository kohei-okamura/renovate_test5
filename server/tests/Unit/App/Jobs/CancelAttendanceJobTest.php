<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CancelAttendanceJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCancelAttendanceJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * CancelAttendanceJob のテスト.
 */
final class CancelAttendanceJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCancelAttendanceJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private CancelAttendanceJob $job;
    private string $reason = 'キャンセル理由';

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelAttendanceJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->job = new CancelAttendanceJob($self->context, $self->domainJob, $self->reason, $self->examples->shifts[0]->id);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCancelAttendanceJobUseCase', function (): void {
            $this->runCancelAttendanceJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->reason, $this->examples->attendances[0]->id)
                ->andReturnNull();

            $this->job->handle($this->runCancelAttendanceJobUseCase);
        });
    }
}

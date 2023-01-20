<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\ConfirmAttendanceJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunConfirmAttendanceJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * ConfirmAttendanceJob のテスト.
 */
final class ConfirmAttendanceJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunConfirmAttendanceJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private ConfirmAttendanceJob $job;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfirmAttendanceJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->job = new ConfirmAttendanceJob($self->context, $self->domainJob, [$self->examples->attendances[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunConfirmAttendanceJobUseCase', function (): void {
            $this->runConfirmAttendanceJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, [$this->examples->attendances[0]->id])
                ->andReturnNull();

            $this->job->handle($this->runConfirmAttendanceJobUseCase);
        });
    }
}

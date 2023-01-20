<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Closure;
use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CancelAttendanceUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Shift\RunCancelAttendanceJobInteractor;

/**
 * {@link \UseCase\Shift\RunCancelAttendanceJobInteractor} Test.
 */
class RunCancelAttendanceJobInteractorTest extends Test
{
    use ContextMixin;
    use CancelAttendanceUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private array $ids;
    private RunCancelAttendanceJobInteractor $interactor;
    private string $reason = 'キャンセル理由';

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunCancelAttendanceJobInteractorTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->ids = [
                $self->examples->attendances[0]->id,
                $self->examples->attendances[1]->id,
            ];
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->cancelAttendanceUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(RunCancelAttendanceJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->domainJob, $this->reason, ...$this->ids);
        });
        $this->should('call CancelAttendanceUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->cancelAttendanceUseCase
                            ->expects('handle')
                            ->with($this->context, $this->reason, ...$this->ids)
                            ->andReturnNull();
                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->reason, ...$this->ids);
        });
    }
}

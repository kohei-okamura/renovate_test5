<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Closure;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfirmShiftUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Shift\RunConfirmShiftJobInteractor;

/**
 * RunConfirmShiftJobInteractor のテスト.
 */
final class RunConfirmShiftJobInteractorTest extends Test
{
    use ConfirmShiftUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private array $ids;
    private RunConfirmShiftJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (RunConfirmShiftJobInteractorTest $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->ids = [
                $self->examples->shifts[0]->id,
                $self->examples->shifts[1]->id,
            ];
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->confirmShiftUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(RunConfirmShiftJobInteractor::class);
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

            $this->interactor->handle($this->context, $this->domainJob, $this->ids);
        });
        $this->should('call ConfirmShiftUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->confirmShiftUseCase
                            ->expects('handle')
                            ->with($this->context, ...$this->ids)
                            ->andReturnNull();
                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->ids);
        });
    }
}

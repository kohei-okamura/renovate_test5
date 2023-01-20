<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use Domain\Job\JobStatus;
use Lib\Exceptions\RuntimeException;
use Lib\Exceptions\ValidationException;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EndJobUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StartJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Job\RunJobInteractor;

/**
 * RunJobInteractor のテスト.
 */
final class RunJobInteractorTest extends Test
{
    use ContextMixin;
    use EndJobUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StartJobUseCaseMixin;
    use UnitSupport;

    private const DATA = ['uri' => 'uri', 'filename' => 'filename'];
    private RunJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunJobInteractorTest $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->startJobUseCase
                ->allows('handle')
                ->andReturn(null)
                ->byDefault();
            $self->endJobUseCase
                ->allows('handle')
                ->andReturn(null)
                ->byDefault();
            $self->callable = Mockery::spy(fn (): array => self::DATA);

            $self->interactor = app(RunJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call callable function', function (): void {
            $this->interactor->handle($this->context, $this->domainJob, $this->callable);
            $this->callable->shouldHaveBeenCalled();
        });
        $this->should('update job status by using StartJobUseCase', function (): void {
            $this->startJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob->id)
                ->andReturn(null);

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->callable
            );
        });
        $this->should('update job status by using EndJobUseCase', function (): void {
            $this->endJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob->id, JobStatus::success(), self::DATA)
                ->andReturn(null);

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->callable
            );
        });
        $this->should('update job status by using EndJobUseCase when Closure throw ValidationException', function (): void {
            $callable = function () {
                throw new ValidationException([]);
            };
            $this->endJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob->id, JobStatus::failure(), typeOf('array'))
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $callable
            );
        });
        $this->should('update job status by using EndJobUseCase when Closure throw RuntimeException', function (): void {
            $callable = function () {
                throw new RuntimeException();
            };
            $this->endJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob->id, JobStatus::failure(), typeOf('array'))
                ->andReturn(null);

            // この should の主目的ではないが、
            // 投げ直される Exception の assert を行う。
            $this->assertThrows(
                RuntimeException::class,
                function () use ($callable): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $callable
                    );
                }
            );
        });
        $this->should('throw RuntimeException when Closure throw RuntimeException', function (): void {
            $callable = function () {
                throw new RuntimeException();
            };

            $this->assertThrows(
                RuntimeException::class,
                function () use ($callable): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $callable
                    );
                }
            );
        });
    }
}

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
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\ImportShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Shift\RunImportShiftJobInteractor;

/**
 * RunImportShiftJobInteractor のテスト.
 */
final class RunImportShiftJobInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use ImportShiftUseCaseMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private const FILE_NAME = 'example.xlsx';

    private DomainJob $domainJob;
    private RunImportShiftJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunImportShiftJobInteractorTest $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->importShiftUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->interactor = app(RunImportShiftJobInteractor::class);
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

            $this->interactor->handle($this->context, self::FILE_NAME, $this->domainJob);
        });
        $this->should('call ImportShiftUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->importShiftUseCase
                            ->expects('handle')
                            ->with($this->context, self::FILE_NAME)
                            ->andReturnNull();
                        $f();
                    }
                );

            $this->interactor->handle($this->context, self::FILE_NAME, $this->domainJob);
        });
    }
}

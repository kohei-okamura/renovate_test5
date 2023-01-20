<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Closure;
use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DeleteUserBillingDepositUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\RunDeleteUserBillingDepositJobInteractor;

/**
 * {@link \UseCase\UserBilling\RunDeleteUserBillingDepositJobInteractor} のテスト.
 */
class RunDeleteUserBillingDepositJobInteractorTest extends Test
{
    use ContextMixin;

    use DeleteUserBillingDepositUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private array $ids;
    private RunDeleteUserBillingDepositJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunDeleteUserBillingDepositJobInteractorTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->ids = [
                $self->examples->userBillings[0]->id,
                $self->examples->userBillings[1]->id,
            ];
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->deleteUserBillingDepositUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(RunDeleteUserBillingDepositJobInteractor::class);
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

            $this->interactor->handle($this->context, $this->domainJob, ...$this->ids);
        });
        $this->should('call DeleteDepositUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->deleteUserBillingDepositUseCase
                            ->expects('handle')
                            ->with($this->context, ...$this->ids)
                            ->andReturnNull();
                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, ...$this->ids);
        });
    }
}

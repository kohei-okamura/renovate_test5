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
use Tests\Unit\Mixins\CreateWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\RunCreateWithdrawalTransactionJobInteractor;

/**
 * {@link \UseCase\UserBilling\RunCreateWithdrawalTransactionJobInteractor} のテスト.
 */
final class RunCreateWithdrawalTransactionJobInteractorTest extends Test
{
    use CreateWithdrawalTransactionUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private array $userBillingIds;
    private RunCreateWithdrawalTransactionJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->userBillingIds = [
                $self->examples->userBillings[0]->id,
                $self->examples->userBillings[1]->id,
            ];
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->createWithdrawalTransactionUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->interactor = app(RunCreateWithdrawalTransactionJobInteractor::class);
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

            $this->interactor->handle($this->context, $this->domainJob, $this->userBillingIds);
        });
        $this->should('call CreateWithdrawalTransactionUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->createWithdrawalTransactionUseCase
                            ->expects('handle')
                            ->with($this->context, $this->userBillingIds)
                            ->andReturn($this->examples->withdrawalTransactions[0]);
                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->userBillingIds);
        });
    }
}

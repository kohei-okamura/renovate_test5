<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Closure;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Mixins\UpdateUserBillingDepositUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\RunUpdateUserBillingDepositJobInteractor;

/**
 * {@link \UseCase\UserBilling\RunUpdateUserBillingDepositJobInteractor} Test.
 */
class RunUpdateUserBillingDepositJobInteractorTest extends Test
{
    use CarbonMixin;
    use UpdateUserBillingDepositUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private array $ids;
    private Carbon $now;
    private RunUpdateUserBillingDepositJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunUpdateUserBillingDepositJobInteractorTest $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->ids = [
                $self->examples->userBillings[0]->id,
                $self->examples->userBillings[1]->id,
            ];
            $self->now = Carbon::now();
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->updateUserBillingDepositUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(RunUpdateUserBillingDepositJobInteractor::class);
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

            $this->interactor->handle($this->context, $this->domainJob, Carbon::now(), $this->ids);
        });
        $this->should('call UpdateUserBillingDepositUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->updateUserBillingDepositUseCase
                            ->expects('handle')
                            ->with($this->context, $this->now, $this->ids)
                            ->andReturnNull();
                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->now, $this->ids);
        });
    }
}

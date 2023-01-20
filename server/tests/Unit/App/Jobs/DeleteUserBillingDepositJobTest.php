<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\DeleteUserBillingDepositJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunDeleteUserBillingDepositJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\DeleteUserBillingDepositJob} のテスト.
 */
final class DeleteUserBillingDepositJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunDeleteUserBillingDepositJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private DeleteUserBillingDepositJob $job;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteUserBillingDepositJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->job = new DeleteUserBillingDepositJob($self->context, $self->domainJob, $self->examples->userBillings[0]->id);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunDeleteDepositJobUseCase', function (): void {
            $this->runDeleteUserBillingDepositJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->examples->userBillings[0]->id)
                ->andReturnNull();

            $this->job->handle($this->runDeleteUserBillingDepositJobUseCase);
        });
    }
}

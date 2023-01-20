<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\UpdateUserBillingDepositJob;
use Domain\Common\Carbon;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunUpdateUserBillingDepositJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\UpdateUserBillingDepositJob} Test.
 */
final class UpdateUserBillingDepositJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunUpdateUserBillingDepositJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private UpdateUserBillingDepositJob $job;
    private Carbon $now;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateUserBillingDepositJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->now = Carbon::now();
            $self->job = new UpdateUserBillingDepositJob($self->context, $self->domainJob, $self->now, [$self->examples->userBillings[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call UpdateUserBillingDepositJob', function (): void {
            $this->runUpdateUserBillingDepositJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->now, [$this->examples->userBillings[0]->id])
                ->andReturnNull();

            $this->job->handle($this->runUpdateUserBillingDepositJobUseCase);
        });
    }
}

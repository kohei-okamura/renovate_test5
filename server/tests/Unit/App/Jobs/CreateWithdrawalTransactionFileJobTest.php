<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateWithdrawalTransactionFileJob;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateWithdrawalTransactionFileJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateWithdrawalTransactionFileJob} のテスト.
 */
final class CreateWithdrawalTransactionFileJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateWithdrawalTransactionFileJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private CreateWithdrawalTransactionFileJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateWithdrawalTransactionFileJob(
                $self->context,
                $self->domainJob,
                $self->examples->withdrawalTransactions[0]->id
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCreateWithdrawalTransactionFileJobUseCase', function (): void {
            $this->runCreateWithdrawalTransactionFileJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->examples->withdrawalTransactions[0]->id)
                ->andReturnNull();

            $this->job->handle($this->runCreateWithdrawalTransactionFileJobUseCase);
        });
    }
}

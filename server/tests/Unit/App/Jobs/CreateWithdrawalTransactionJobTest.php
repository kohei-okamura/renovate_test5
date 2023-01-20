<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateWithdrawalTransactionJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateWithdrawalTransactionJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateWithdrawalTransactionJob} のテスト.
 */
final class CreateWithdrawalTransactionJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateWithdrawalTransactionJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private CreateWithdrawalTransactionJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateWithdrawalTransactionJob($self->context, $self->domainJob, [$self->examples->userBillings[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCreateWithdrawalTransactionJobUseCase', function (): void {
            $this->runCreateWithdrawalTransactionJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, [$this->examples->userBillings[0]->id])
                ->andReturnNull();

            $this->job->handle($this->runCreateWithdrawalTransactionJobUseCase);
        });
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateUserBillingReceiptJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateUserBillingReceiptJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * CreateUserBillingReceiptJob のテスト.
 */
final class CreateUserBillingReceiptJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateUserBillingReceiptJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private CreateUserBillingReceiptJob $job;
    private array $ids;
    private Carbon $issuedOn;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingReceiptJobTest $self): void {
            $self->ids = [$self->examples->userBillings[0]->id];
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateUserBillingReceiptJob(
                $self->context,
                $self->domainJob,
                $self->ids,
                $self->issuedOn
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCreateUserBillingReceiptJobUseCase', function (): void {
            $this->runCreateUserBillingReceiptJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->ids, $this->issuedOn)
                ->andReturnNull();

            $this->job->handle($this->runCreateUserBillingReceiptJobUseCase);
        });
    }
}

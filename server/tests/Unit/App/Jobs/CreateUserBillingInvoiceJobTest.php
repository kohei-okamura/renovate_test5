<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateUserBillingInvoiceJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateUserBillingInvoiceJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * CreateUserBillingInvoiceJob のテスト.
 */
final class CreateUserBillingInvoiceJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateUserBillingInvoiceJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private CreateUserBillingInvoiceJob $job;
    private array $ids;
    private Carbon $issuedOn;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingInvoiceJobTest $self): void {
            $self->ids = [
                $self->examples->userBillings[0]->id,
            ];
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateUserBillingInvoiceJob(
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
        $this->should('call RunCreateUserBillingInvoiceJobUseCase', function (): void {
            $this->runCreateUserBillingInvoiceJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->ids, $this->issuedOn)
                ->andReturnNull();

            $this->job->handle($this->runCreateUserBillingInvoiceJobUseCase);
        });
    }
}

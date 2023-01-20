<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateUserBillingStatementJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateUserBillingStatementJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * CreateUserBillingStatementJob のテスト.
 */
final class CreateUserBillingStatementJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateUserBillingStatementJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private Carbon $issuedOn;
    private CreateUserBillingStatementJob $job;
    private array $parameters;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingStatementJobTest $self): void {
            $self->parameters = [
                'ids' => [$self->examples->userBillings[0]->id],
            ];
            $self->issuedOn = Carbon::now();
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateUserBillingStatementJob(
                $self->context,
                $self->domainJob,
                $self->parameters,
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
        $this->should('call RunCreateUserBillingStatementJobUseCase', function (): void {
            $this->runCreateUserBillingStatementJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->parameters, $this->issuedOn)
                ->andReturnNull();

            $this->job->handle($this->runCreateUserBillingStatementJobUseCase);
        });
    }
}

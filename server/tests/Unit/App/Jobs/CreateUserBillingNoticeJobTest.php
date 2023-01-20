<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateUserBillingNoticeJob;
use Domain\Common\Carbon;
use Domain\Job\Job as DomainJob;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateUserBillingNoticeJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateUserBillingNoticeJob} のテスト.
 */
final class CreateUserBillingNoticeJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use RunCreateUserBillingNoticeJobUseCaseMixin;

    private DomainJob $domainJob;
    private array $ids;
    private Carbon $issuedOn;
    private CreateUserBillingNoticeJob $job;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingNoticeJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->ids = Seq::fromArray($self->examples->userBillings)->map(fn (UserBilling $x): int => $x->id)->toArray();
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->job = new CreateUserBillingNoticeJob(
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
        $this->should('call RunDeleteDepositJobUseCase', function (): void {
            $this->runCreateUserBillingNoticeJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->ids, $this->issuedOn)
                ->andReturnNull();

            $this->job->handle($this->runCreateUserBillingNoticeJobUseCase);
        });
    }
}

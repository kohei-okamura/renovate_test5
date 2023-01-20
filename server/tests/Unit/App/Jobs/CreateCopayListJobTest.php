<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateCopayListJob;
use Domain\Job\Job;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateCopayListJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateCopayListJob} のテスト.
 */
final class CreateCopayListJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use RunCreateCopayListJobUseCaseMixin;

    private Job $domainJob;
    private CreateCopayListJob $job;
    private int $billingId;
    private array $ids;
    private bool $isDivided;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->dwsBillingStatements[0]->dwsBillingId;
            $self->ids = [$self->examples->dwsBillingStatements[0]->id];
            $self->isDivided = false;
            $self->job = new CreateCopayListJob(
                $self->context,
                $self->domainJob,
                $self->billingId,
                $self->ids,
                $self->isDivided
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call CreateCopayListJob', function (): void {
            $this->runCreateCopayListJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->ids,
                    $this->isDivided
                )
                ->andReturnNull();

            $this->job->handle($this->runCreateCopayListJobUseCase);
        });
    }
}

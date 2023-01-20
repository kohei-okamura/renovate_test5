<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\RefreshLtcsBillingStatementJob;
use Domain\Job\Job;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunRefreshLtcsBillingStatementJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\RefreshLtcsBillingStatementJob} のテスト.
 */
final class RefreshLtcsBillingStatementJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use RunRefreshLtcsBillingStatementJobUseCaseMixin;

    private Job $domainJob;
    private RefreshLtcsBillingStatementJob $job;
    private int $billingId;
    private array $ids;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->ltcsBillingStatements[0]->billingId;
            $self->ids = [$self->examples->ltcsBillingStatements[0]->id];
            $self->job = new RefreshLtcsBillingStatementJob(
                $self->context,
                $self->domainJob,
                $self->billingId,
                $self->ids
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RefreshLtcsBillingStatementJob', function (): void {
            $this->runRefreshLtcsBillingStatementJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->ids,
                )
                ->andReturnNull();

            $this->job->handle($this->runRefreshLtcsBillingStatementJobUseCase);
        });
    }
}

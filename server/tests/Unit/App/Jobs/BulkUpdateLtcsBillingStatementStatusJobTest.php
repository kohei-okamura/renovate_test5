<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\BulkUpdateLtcsBillingStatementStatusJob;
use Domain\Billing\LtcsBillingStatus;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunBulkUpdateLtcsBillingStatementStatusJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\BulkUpdateLtcsBillingStatementStatusJob} のテスト.
 */
final class BulkUpdateLtcsBillingStatementStatusJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunBulkUpdateLtcsBillingStatementStatusJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private BulkUpdateLtcsBillingStatementStatusJob $job;
    private int $billingId;
    private int $bundleId;
    private array $ids;
    private LtcsBillingStatus $status;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->ltcsBillingStatements[0]->billingId;
            $self->bundleId = $self->examples->ltcsBillingStatements[0]->bundleId;
            $self->ids = [$self->examples->ltcsBillingStatements[0]->id];
            $self->status = LtcsBillingStatus::fixed();
            $self->job = new BulkUpdateLtcsBillingStatementStatusJob(
                $self->context,
                $self->domainJob,
                $self->billingId,
                $self->bundleId,
                $self->ids,
                $self->status
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call BulkUpdateLtcsBillingStatementStatusJob', function (): void {
            $this->runBulkUpdateLtcsBillingStatementStatusJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->bundleId,
                    $this->ids,
                    $this->status
                )
                ->andReturnNull();

            $this->job->handle($this->runBulkUpdateLtcsBillingStatementStatusJobUseCase);
        });
    }
}

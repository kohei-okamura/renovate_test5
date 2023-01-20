<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\BulkUpdateDwsBillingServiceReportStatusJob;
use Domain\Billing\DwsBillingStatus;
use Domain\Job\Job;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunBulkUpdateDwsBillingServiceReportStatusJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\BulkUpdateDwsBillingServiceReportStatusJob} のテスト.
 */
final class BulkUpdateDwsBillingServiceReportStatusJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use RunBulkUpdateDwsBillingServiceReportStatusJobUseCaseMixin;

    private Job $domainJob;
    private BulkUpdateDwsBillingServiceReportStatusJob $job;
    private int $billingId;
    private array $ids;
    private DwsBillingStatus $status;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->dwsBillingServiceReports[0]->dwsBillingId;
            $self->ids = [$self->examples->dwsBillingServiceReports[0]->id];
            $self->status = DwsBillingStatus::fixed();
            $self->job = new BulkUpdateDwsBillingServiceReportStatusJob(
                $self->context,
                $self->domainJob,
                $self->billingId,
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
        $this->should('call BulkUpdateDwsBillingServiceReportStatusJob', function (): void {
            $this->runBulkUpdateDwsBillingServiceReportStatusJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->ids,
                    $this->status
                )
                ->andReturnNull();

            $this->job->handle($this->runBulkUpdateDwsBillingServiceReportStatusJobUseCase);
        });
    }
}

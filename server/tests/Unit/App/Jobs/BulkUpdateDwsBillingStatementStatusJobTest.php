<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\BulkUpdateDwsBillingStatementStatusJob;
use Domain\Billing\DwsBillingStatus;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunBulkUpdateDwsBillingStatementStatusJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\BulkUpdateDwsBillingStatementStatusJob} のテスト.
 */
final class BulkUpdateDwsBillingStatementStatusJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunBulkUpdateDwsBillingStatementStatusJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private BulkUpdateDwsBillingStatementStatusJob $job;
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
            $self->billingId = $self->examples->dwsBillingStatements[0]->dwsBillingId;
            $self->ids = [$self->examples->dwsBillingStatements[0]->id];
            $self->status = DwsBillingStatus::fixed();
            $self->job = new BulkUpdateDwsBillingStatementStatusJob(
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
        $this->should('call BulkUpdateDwsBillingStatementStatusJob', function (): void {
            $this->runBulkUpdateDwsBillingStatementStatusJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->ids,
                    $this->status
                )
                ->andReturnNull();

            $this->job->handle($this->runBulkUpdateDwsBillingStatementStatusJobUseCase);
        });
    }
}

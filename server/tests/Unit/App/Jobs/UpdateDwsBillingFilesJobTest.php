<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\UpdateDwsBillingFilesJob;
use Domain\Job\Job as DomainJob;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunUpdateDwsBillingFilesJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\UpdateDwsBillingFilesJob} のテスト.
 */
final class UpdateDwsBillingFilesJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunUpdateDwsBillingFilesJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private int $billingId;

    private UpdateDwsBillingFilesJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->domainJob = DomainJob::create();
            $self->billingId = $self->examples->dwsBillings[0]->id;
        });
        self::beforeEachSpec(function (self $self): void {
            $self->job = new UpdateDwsBillingFilesJob($self->context, $self->domainJob, $self->billingId);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call UseCase', function (): void {
            $this->runUpdateDwsBillingFilesJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->billingId)
                ->andReturnNull();

            $this->job->handle($this->runUpdateDwsBillingFilesJobUseCase);
        });
    }
}

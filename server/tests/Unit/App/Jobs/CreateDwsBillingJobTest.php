<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateDwsBillingJob;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Job\Job;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateDwsBillingJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateDwsBillingJob} のテスト.
 */
final class CreateDwsBillingJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunCreateDwsBillingJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private int $officeId;
    private Carbon $transactedIn;
    private CarbonRange $fixedAt;

    private CreateDwsBillingJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->domainJob = Job::create();
            $self->officeId = $self->examples->offices[0]->id;
            $self->transactedIn = Carbon::create();
            $self->fixedAt = CarbonRange::create();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->job = new CreateDwsBillingJob(
                $self->context,
                $self->domainJob,
                $self->officeId,
                $self->transactedIn,
                $self->fixedAt
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call UseCase', function (): void {
            $this->runCreateDwsBillingJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->officeId, $this->transactedIn, $this->fixedAt)
                ->andReturnNull();

            $this->job->handle($this->runCreateDwsBillingJobUseCase);
        });
    }
}

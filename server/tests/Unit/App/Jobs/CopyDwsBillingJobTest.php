<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CopyDwsBillingJob;
use Domain\Job\Job;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCopyDwsBillingJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CopyDwsBillingJob} のテスト.
 */
final class CopyDwsBillingJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunCopyDwsBillingJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private int $id;

    private CopyDwsBillingJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->domainJob = Job::create();
            $self->id = $self->examples->dwsBillings[0]->id;
        });
        self::beforeEachSpec(function (self $self): void {
            $self->job = new CopyDwsBillingJob(
                $self->context,
                $self->domainJob,
                $self->id,
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
            $this->runCopyDwsBillingJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->id)
                ->andReturnNull();

            $this->job->handle($this->runCopyDwsBillingJobUseCase);
        });
    }
}

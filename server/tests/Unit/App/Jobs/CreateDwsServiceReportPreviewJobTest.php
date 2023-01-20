<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateDwsServiceReportPreviewJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateDwsServiceReportPreviewJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateDwsServiceReportPreviewJob} のテスト.
 */
final class CreateDwsServiceReportPreviewJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateDwsServiceReportPreviewJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private CreateDwsServiceReportPreviewJob $job;
    private int $officeId;
    private int $userId;
    private Carbon $providedIn;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->officeId = $self->examples->dwsProvisionReports[0]->officeId;
            $self->userId = $self->examples->dwsProvisionReports[0]->userId;
            $self->providedIn = $self->examples->dwsProvisionReports[0]->providedIn;
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateDwsServiceReportPreviewJob(
                $self->context,
                $self->domainJob,
                $self->officeId,
                $self->userId,
                $self->providedIn,
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCreateLtcsProvisionReportSheetJobUseCase', function (): void {
            $this->runCreateDwsServiceReportPreviewJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                )
                ->andReturnNull();

            $this->job->handle($this->runCreateDwsServiceReportPreviewJobUseCase);
        });
    }
}

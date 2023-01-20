<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateLtcsProvisionReportSheetJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateLtcsProvisionReportSheetJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\CreateLtcsProvisionReportSheetJob} のテスト.
 */
final class CreateLtcsProvisionReportSheetJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateLtcsProvisionReportSheetJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private CreateLtcsProvisionReportSheetJob $job;
    private int $officeId;
    private int $userId;
    private Carbon $providedIn;
    private Carbon $issuedOn;
    private bool $needsMaskingInsNumber;
    private bool $needsMaskingInsName;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateLtcsProvisionReportSheetJobTest $self): void {
            $self->officeId = $self->examples->ltcsProvisionReports[0]->officeId;
            $self->userId = $self->examples->ltcsProvisionReports[0]->userId;
            $self->providedIn = $self->examples->ltcsProvisionReports[0]->providedIn;
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->domainJob = $self->examples->jobs[0];
            $self->needsMaskingInsNumber = true;
            $self->needsMaskingInsName = true;
            $self->job = new CreateLtcsProvisionReportSheetJob(
                $self->context,
                $self->domainJob,
                $self->officeId,
                $self->userId,
                $self->providedIn,
                $self->issuedOn,
                $self->needsMaskingInsNumber,
                $self->needsMaskingInsName,
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
            $this->runCreateLtcsProvisionReportSheetJobUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->domainJob,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                    $this->issuedOn,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                )
                ->andReturnNull();

            $this->job->handle($this->runCreateLtcsProvisionReportSheetJobUseCase);
        });
    }
}

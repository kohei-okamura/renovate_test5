<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Closure;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateDwsServiceReportPreviewPdfUseCaseMixin;
use Tests\Unit\Mixins\GenerateFileNameContainsUserNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobInteractor;

/**
 * {@link \UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobInteractor} のテスト.
 */
final class RunCreateDwsServiceReportPreviewJobInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateDwsServiceReportPreviewPdfUseCaseMixin;
    use GenerateFileNameContainsUserNameUseCaseMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private const PATH = 'dummies/download/dummy';
    private const FILENAME = 'dummy.pdf';

    private DomainJob $domainJob;
    private array $officeId;
    private array $userId;
    private array $providedIn;
    private RunCreateDwsServiceReportPreviewJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->generateDwsServiceReportPreviewPdfUseCase
                ->allows('handle')
                ->andReturn(self::PATH)
                ->byDefault();
            $self->generateFileNameContainsUserNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(RunCreateDwsServiceReportPreviewJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturn(null);

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->examples->dwsProvisionReports[0]->officeId,
                $this->examples->dwsProvisionReports[0]->userId,
                $this->examples->dwsProvisionReports[0]->providedIn
            );
        });
        $this->should('call GenerateDwsServiceReportPreviewPdfUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->generateDwsServiceReportPreviewPdfUseCase
                            ->expects('handle')
                            ->with(
                                $this->context,
                                $this->examples->dwsProvisionReports[0]->officeId,
                                $this->examples->dwsProvisionReports[0]->userId,
                                $this->examples->dwsProvisionReports[0]->providedIn,
                            )
                            ->andReturn(self::PATH);

                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->examples->dwsProvisionReports[0]->officeId,
                $this->examples->dwsProvisionReports[0]->userId,
                $this->examples->dwsProvisionReports[0]->providedIn
            );
        });

        $this->should('call GenerateFileNameContainsUserNameUseCase', function (): void {
            $uri = $this->context->uri('dws-service-report-previews/download/' . self::PATH);
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($uri): void {
                        $this->generateFileNameContainsUserNameUseCase
                            ->expects('handle')
                            ->with(
                                $this->context,
                                $this->examples->dwsProvisionReports[0]->userId,
                                'dws_service_report_preview_pdf',
                                ['providedIn' => $this->examples->dwsProvisionReports[0]->providedIn]
                            )
                            ->andReturn(self::FILENAME);

                        $res = $f();

                        // 正しい値を返すことも検証
                        $this->assertSame(
                            ['uri' => $uri, 'filename' => self::FILENAME],
                            $res
                        );
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->examples->dwsProvisionReports[0]->officeId,
                $this->examples->dwsProvisionReports[0]->userId,
                $this->examples->dwsProvisionReports[0]->providedIn,
            );
        });
    }
}

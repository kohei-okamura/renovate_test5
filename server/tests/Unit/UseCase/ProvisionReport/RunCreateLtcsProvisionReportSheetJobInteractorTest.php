<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Closure;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameContainsUserNameUseCaseMixin;
use Tests\Unit\Mixins\GenerateLtcsProvisionReportSheetPdfUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobInteractor;

/**
 * {@link \UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobInteractor} のテスト.
 */
final class RunCreateLtcsProvisionReportSheetJobInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateLtcsProvisionReportSheetPdfUseCaseMixin;
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
    private Carbon $issuedOn;
    private bool $needsMaskingInsNumber;
    private bool $needsMaskingInsName;
    private RunCreateLtcsProvisionReportSheetJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunCreateLtcsProvisionReportSheetJobInteractorTest $self): void {
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->domainJob = $self->examples->jobs[0];
            $self->needsMaskingInsNumber = true;
            $self->needsMaskingInsName = true;
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->generateLtcsProvisionReportSheetPdfUseCase
                ->allows('handle')
                ->andReturn(self::PATH)
                ->byDefault();
            $self->generateFileNameContainsUserNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(RunCreateLtcsProvisionReportSheetJobInteractor::class);
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
                $this->examples->ltcsProvisionReports[0]->officeId,
                $this->examples->ltcsProvisionReports[0]->userId,
                $this->examples->ltcsProvisionReports[0]->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName
            );
        });
        $this->should('call GenerateLtcsProvisionReportSheetPdfUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->generateLtcsProvisionReportSheetPdfUseCase
                            ->expects('handle')
                            ->with(
                                $this->context,
                                $this->examples->ltcsProvisionReports[0]->officeId,
                                $this->examples->ltcsProvisionReports[0]->userId,
                                $this->examples->ltcsProvisionReports[0]->providedIn,
                                $this->issuedOn,
                                $this->needsMaskingInsNumber,
                                $this->needsMaskingInsName
                            )
                            ->andReturn(self::PATH);

                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->examples->ltcsProvisionReports[0]->officeId,
                $this->examples->ltcsProvisionReports[0]->userId,
                $this->examples->ltcsProvisionReports[0]->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName
            );
        });

        $this->should('call GenerateFileNameContainsUserNameUseCase', function (): void {
            $uri = $this->context->uri('ltcs-provision-reports/download/' . self::PATH);
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($uri): void {
                        $this->generateFileNameContainsUserNameUseCase
                            ->expects('handle')
                            ->with(
                                $this->context,
                                $this->examples->ltcsProvisionReports[0]->userId,
                                'ltcs_provision_report_sheet_pdf',
                                ['providedIn' => $this->examples->ltcsProvisionReports[0]->providedIn]
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
                $this->examples->ltcsProvisionReports[0]->officeId,
                $this->examples->ltcsProvisionReports[0]->userId,
                $this->examples->ltcsProvisionReports[0]->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName
            );
        });
    }
}

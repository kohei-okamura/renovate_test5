<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Lib\Logging;
use UseCase\File\GenerateFileNameContainsUserNameUseCase;
use UseCase\Job\RunJobUseCase;

/**
 * 介護保険サービス：サービス提供票生成ジョブ実行ユースケース実装.
 */
final class RunCreateLtcsProvisionReportSheetJobInteractor implements RunCreateLtcsProvisionReportSheetJobUseCase
{
    use Logging;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase $generateLtcsProvisionReportSheetPdfUseCase
     * @param \UseCase\File\GenerateFileNameContainsUserNameUseCase $generateFileNameContainsUserNameUseCase
     */
    public function __construct(
        private RunJobUseCase $runJobUseCase,
        private GenerateLtcsProvisionReportSheetPdfUseCase $generateLtcsProvisionReportSheetPdfUseCase,
        private GenerateFileNameContainsUserNameUseCase $generateFileNameContainsUserNameUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Carbon $issuedOn,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): void {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $officeId, $userId, $providedIn, $issuedOn, $needsMaskingInsNumber, $needsMaskingInsName): array {
                $path = $this->generateLtcsProvisionReportSheetPdfUseCase->handle(
                    $context,
                    $officeId,
                    $userId,
                    $providedIn,
                    $issuedOn,
                    $needsMaskingInsNumber,
                    $needsMaskingInsName
                );
                $filename = $this->generateFileNameContainsUserNameUseCase->handle(
                    $context,
                    $userId,
                    'ltcs_provision_report_sheet_pdf',
                    ['providedIn' => $providedIn]
                );
                $this->logger()->info(
                    '介護保険サービス：サービス提供票生成ジョブ終了',
                    ['filename' => $filename] + $context->logContext()
                );
                return [
                    'uri' => $context->uri("ltcs-provision-reports/download/{$path}"),
                    'filename' => $filename,
                ];
            }
        );
    }
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
 * サービス提供実績記録票（プレビュー版）生成ジョブ実行ユースケース実装.
 */
final class RunCreateDwsServiceReportPreviewJobInteractor implements RunCreateDwsServiceReportPreviewJobUseCase
{
    use Logging;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfUseCase $generateDwsServiceReportPreviewPdfUseCase
     * @param \UseCase\File\GenerateFileNameContainsUserNameUseCase $generateFileNameContainsUserNameUseCase
     */
    public function __construct(
        private RunJobUseCase $runJobUseCase,
        private GenerateDwsServiceReportPreviewPdfUseCase $generateDwsServiceReportPreviewPdfUseCase,
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
    ): void {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $officeId, $userId, $providedIn): array {
                $path = $this->generateDwsServiceReportPreviewPdfUseCase->handle(
                    $context,
                    $officeId,
                    $userId,
                    $providedIn,
                );
                $filename = $this->generateFileNameContainsUserNameUseCase->handle(
                    $context,
                    $userId,
                    'dws_service_report_preview_pdf',
                    ['providedIn' => $providedIn]
                );
                $this->logger()->info(
                    'サービス提供実績記録票（プレビュー版）生成ジョブ終了',
                    ['filename' => $filename] + $context->logContext()
                );
                return [
                    'uri' => $context->uri("dws-service-report-previews/download/{$path}"),
                    'filename' => $filename,
                ];
            }
        );
    }
}

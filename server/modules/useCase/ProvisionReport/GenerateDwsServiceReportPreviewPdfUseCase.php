<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;

/**
 * サービス提供実績記録票（プレビュー版） PDF 生成ユースケース.
 */
interface GenerateDwsServiceReportPreviewPdfUseCase
{
    /**
     * サービス提供実績記録票（プレビュー版） PDF を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @throws \Throwable
     * @return string
     */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
    ): string;
}

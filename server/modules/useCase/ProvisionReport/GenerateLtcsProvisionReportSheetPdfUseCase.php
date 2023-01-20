<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;

/**
 * サービス提供票 PDF 生成ユースケース.
 */
interface GenerateLtcsProvisionReportSheetPdfUseCase
{
    /**
     * サービス提供票 PDF を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Common\Carbon $issuedOn
     * @param bool $needsMaskingInsNumber
     * @param bool $needsMaskingInsName
     * @return string
     */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Carbon $issuedOn,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): string;
}

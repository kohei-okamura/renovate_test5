<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス提供票別表PDF組み立てユースケース.
 */
interface BuildLtcsProvisionReportSheetAppendixPdfParamUseCase
{
    /**
     * サービス提供票別表PDFを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report 予実
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth 月初時点の介護保険被保険者証
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth 月末時点の介護保険被保険者証
     * @param \Domain\Office\Office $office 事業所
     * @param \Domain\User\User $user 利用者
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails サービス詳細
     * @param \ScalikePHP\Map&string[] $serviceCodeMap key=サービスコード value=サービス名称
     * @param bool $needsMaskingInsNumber
     * @param bool $needsMaskingInsName
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf
     */
    public function handle(
        Context $context,
        LtcsProvisionReport $report,
        Option $insCardAtFirstOfMonth,
        LtcsInsCard $insCardAtLastOfMonth,
        Office $office,
        User $user,
        Seq $serviceDetails,
        Map $serviceCodeMap,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): LtcsProvisionReportSheetAppendixPdf;
}

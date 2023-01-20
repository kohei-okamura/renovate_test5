<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス提供票PDF組み立てユースケース.
 */
interface BuildLtcsProvisionReportSheetPdfParamUseCase
{
    /**
     * サービス提供票PDFを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth 月初時点の介護保険被保険者証
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth 月末時点の介護保険被保険者証
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForPlan サービス詳細一覧（予定）
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForResult サービス詳細一覧（実績）
     * @param \Domain\User\User $user 利用者
     * @param \Domain\Common\Carbon $createdOn 作成日（発行日）
     * @param \Domain\ProvisionReport\LtcsProvisionReport $provisionReport 介護保険予実
     * @param \Domain\Office\Office $office サービス提供を行った事業所
     * @param \ScalikePHP\Map&string[] $serviceCodeMap key=サービスコード value=サービス名称
     * @param bool $needsMaskingInsNumber
     * @param bool $needsMaskingInsName
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetPdf[]&\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Option $insCardAtFirstOfMonth,
        LtcsInsCard $insCardAtLastOfMonth,
        Seq $serviceDetailsForPlan,
        Seq $serviceDetailsForResult,
        User $user,
        Carbon $createdOn,
        LtcsProvisionReport $provisionReport,
        Office $office,
        Map $serviceCodeMap,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): Seq;
}

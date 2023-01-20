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
 * 介護保険サービス：サービス提供票別表PDF組み立てユースケース実装.
 */
final class BuildLtcsProvisionReportSheetAppendixPdfParamInteractor implements BuildLtcsProvisionReportSheetAppendixPdfParamUseCase
{
    /**
     * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase} constructor.
     *
     * @param \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase $buildAppendixUseCase
     */
    public function __construct(
        private readonly BuildLtcsProvisionReportSheetAppendixUseCase $buildAppendixUseCase
    ) {
    }

    /** {@inheritdoc} */
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
    ): LtcsProvisionReportSheetAppendixPdf {
        $appendix = $this->buildAppendixUseCase->handle(
            $context,
            $report,
            $insCardAtFirstOfMonth,
            $insCardAtLastOfMonth,
            $office,
            $user,
            $serviceDetails,
            $serviceCodeMap
        );
        return LtcsProvisionReportSheetAppendixPdf::from(
            $appendix,
            $needsMaskingInsNumber,
            $needsMaskingInsName
        );
    }
}

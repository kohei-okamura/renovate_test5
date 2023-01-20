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
use Domain\ProvisionReport\LtcsProvisionReportSheetPdf;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Office\GetOfficeListUseCase;

/**
 * 介護保険サービス：サービス提供票PDF組み立てユースケース実装.
 */
final class BuildLtcsProvisionReportSheetPdfParamInteractor implements BuildLtcsProvisionReportSheetPdfParamUseCase
{
    private GetOfficeListUseCase $getOfficeListUseCase;

    /**
     * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase} constructor.
     *
     * @param \UseCase\Office\GetOfficeListUseCase $getOfficeListUseCase
     */
    public function __construct(GetOfficeListUseCase $getOfficeListUseCase)
    {
        $this->getOfficeListUseCase = $getOfficeListUseCase;
    }

    /** {@inheritdoc} */
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
    ): Seq {
        $carePlanAuthorOffice = $this->getOffice(
            $context,
            Option::from($insCardAtLastOfMonth->carePlanAuthorOfficeId)
        );
        return LtcsProvisionReportSheetPdf::from(
            $insCardAtFirstOfMonth,
            $insCardAtLastOfMonth,
            $serviceDetailsForPlan,
            $serviceDetailsForResult,
            $user,
            $createdOn,
            $provisionReport,
            $serviceCodeMap,
            $office,
            $carePlanAuthorOffice,
            $needsMaskingInsNumber,
            $needsMaskingInsName
        );
    }

    /**
     * 居宅介護支援事業者事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[]&\ScalikePHP\Option $id
     * @return \Domain\Office\Office&\ScalikePHP\Option
     */
    private function getOffice(Context $context, Option $id): Option
    {
        return $id->map(function (int $id) use ($context): Office {
            return $this->getOfficeListUseCase
                ->handle($context, $id)
                ->headOption()
                ->getOrElse(
                    function () use ($id): void {
                        throw new NotFoundException("office({$id}) not found");
                    }
                );
        });
    }
}

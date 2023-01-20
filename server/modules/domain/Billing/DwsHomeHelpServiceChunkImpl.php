<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\CarbonRange;
use Domain\Entity;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護） 実装.
 */
class DwsHomeHelpServiceChunkImpl extends Entity implements DwsHomeHelpServiceChunk
{
    use DwsHomeHelpServiceChunkComposeMixin;
    use DwsHomeHelpServiceChunkGetDurationsMixin;

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param \Domain\ProvisionReport\DwsProvisionReportItem $item
     * @return \Domain\Billing\DwsHomeHelpServiceChunk
     */
    public static function from(DwsProvisionReport $report, DwsProvisionReportItem $item): DwsHomeHelpServiceChunk
    {
        $optionsSeq = Seq::fromArray($item->options);
        return DwsHomeHelpServiceChunkImpl::create([
            'userId' => $report->userId,
            'category' => DwsServiceCodeCategory::fromDwsProjectServiceCategory($item->category),
            'buildingType' => self::calculateBuildingType($item->options),
            'isEmergency' => $optionsSeq->exists(
                fn (ServiceOption $x): bool => $x === ServiceOption::emergency()
            ),
            'isPlannedByNovice' => $optionsSeq->exists(
                fn (ServiceOption $x): bool => $x === ServiceOption::plannedByNovice()
            ),
            'isFirst' => $optionsSeq->exists(fn (ServiceOption $x): bool => $x === ServiceOption::firstTime()),
            'isWelfareSpecialistCooperation' => $optionsSeq->exists(
                fn (ServiceOption $x): bool => $x === ServiceOption::welfareSpecialistCooperation()
            ),
            'range' => CarbonRange::create([
                'start' => $item->schedule->start,
                'end' => $item->schedule->end,
            ]),
            'fragments' => Seq::from(
                DwsHomeHelpServiceFragment::create([
                    'headcount' => $item->headcount,
                    'providerType' => self::calculateProviderType($item->options),
                    'isSecondary' => false,
                    'range' => CarbonRange::create([
                        'start' => $item->schedule->start,
                        'end' => $item->schedule->end,
                    ]),
                ]),
            ),
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'category',
            'buildingType',
            'isEmergency',
            'isFirst',
            'isWelfareSpecialistCooperation',
            'isPlannedByNovice',
            'range',
            'fragments',
        ];
    }

    /**
     * 建物提供区分を特定する.
     *
     * @param array|\Domain\Shift\ServiceOption[] $serviceOptions
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType
     */
    private static function calculateBuildingType(array $serviceOptions): DwsHomeHelpServiceBuildingType
    {
        foreach ($serviceOptions as $serviceOption) {
            if ($serviceOption === ServiceOption::over20()) {
                return DwsHomeHelpServiceBuildingType::over20();
            } elseif ($serviceOption === ServiceOption::over50()) {
                return DwsHomeHelpServiceBuildingType::over50();
            }
        }
        return DwsHomeHelpServiceBuildingType::none();
    }

    /**
     * 提供者区分を算出する.
     *
     * @param array|\Domain\Shift\ServiceOption[] $serviceOptions
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType
     */
    private static function calculateProviderType(array $serviceOptions): DwsHomeHelpServiceProviderType
    {
        foreach ($serviceOptions as $serviceOption) {
            if ($serviceOption === ServiceOption::providedByBeginner()) {
                return DwsHomeHelpServiceProviderType::beginner();
            } elseif ($serviceOption === ServiceOption::providedByCareWorkerForPwsd()) {
                return DwsHomeHelpServiceProviderType::careWorkerForPwsd();
            }
        }
        return DwsHomeHelpServiceProviderType::none();
    }
}

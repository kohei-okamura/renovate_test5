<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Model;
use Domain\Shift\HasServiceOptions;
use Domain\Shift\ServiceOption;

/**
 * 介護保険サービス：予実：サービス情報.
 *
 * @property-read null|int $ownExpenseProgramId 自費サービス情報ID
 * @property-read \Domain\Common\TimeRange $slot 時間帯
 * @property-read \Domain\ServiceCodeDictionary\Timeframe $timeframe 算定時間帯
 * @property-read \Domain\Project\LtcsProjectServiceCategory $category サービス区分
 * @property-read \Domain\Project\LtcsProjectAmount[] $amounts サービス提供量
 * @property-read int $headcount 提供人数
 * @property-read null|\Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read \Domain\Shift\ServiceOption[] $options サービスオプション
 * @property-read string $note 備考
 * @property-read \Domain\Common\Carbon[] $plans 予定年月日
 * @property-read \Domain\Common\Carbon[] $results 実績年月日
 */
final class LtcsProvisionReportEntry extends Model
{
    use HasServiceOptions;

    /**
     * 同一建物減算区分を返す.
     *
     * @return \Domain\ProvisionReport\LtcsBuildingSubtraction
     */
    public function buildingSubtraction(): LtcsBuildingSubtraction
    {
        if ($this->hasOption(ServiceOption::over20())) {
            return LtcsBuildingSubtraction::subtraction1();
        } elseif ($this->hasOption(ServiceOption::over50())) {
            return LtcsBuildingSubtraction::subtraction2();
        } else {
            return LtcsBuildingSubtraction::none();
        }
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'ownExpenseProgramId',
            'slot',
            'timeframe',
            'category',
            'amounts',
            'headcount',
            'serviceCode',
            'options',
            'note',
            'plans',
            'results',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'slot' => true,
            'timeframe' => true,
            'category' => true,
            'amounts' => true,
            'headcount' => true,
            'ownExpenseProgramId' => true,
            'serviceCode' => true,
            'options' => true,
            'note' => true,
            'plans' => 'date',
            'results' => 'date',
        ];
    }
}

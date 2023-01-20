<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Model;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\HasServiceOptions;

/**
 * 障害福祉サービス：予実：要素.
 *
 * @property-read \Domain\Common\Schedule $schedule スケジュール
 * @property-read \Domain\Project\DwsProjectServiceCategory $category サービス区分
 * @property-read int $headcount 提供人数
 * @property-read int $movingDurationMinutes 移動介護時間数
 * @property-read null|int $ownExpenseProgramId 自費サービス情報 ID
 * @property-read \Domain\Shift\ServiceOption[] $options サービスオプション
 * @property-read string $note 備考
 */
final class DwsProvisionReportItem extends Model
{
    use HasServiceOptions;

    /**
     * サービス区分が居宅介護であるかどうかを判定する.
     *
     * @return bool
     */
    public function isHomeHelpService(): bool
    {
        return $this->category === DwsProjectServiceCategory::physicalCare()
            || $this->category === DwsProjectServiceCategory::housework()
            || $this->category === DwsProjectServiceCategory::accompanyWithPhysicalCare()
            || $this->category === DwsProjectServiceCategory::accompany();
    }

    /**
     * サービス区分が重度訪問介護であるかどうかを判定する.
     *
     * @return bool
     */
    public function isVisitingCareForPwsd(): bool
    {
        return $this->category === DwsProjectServiceCategory::visitingCareForPwsd();
    }

    /**
     * サービス区分が自費であるかどうかを判定する.
     *
     * @return bool
     */
    public function isOwnExpense(): bool
    {
        return $this->category === DwsProjectServiceCategory::ownExpense();
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'schedule',
            'category',
            'headcount',
            'movingDurationMinutes',
            'ownExpenseProgramId',
            'options',
            'note',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'schedule' => true,
            'category' => true,
            'headcount' => true,
            'movingDurationMinutes' => true,
            'ownExpenseProgramId' => true,
            'options' => true,
            'note' => true,
        ];
    }
}

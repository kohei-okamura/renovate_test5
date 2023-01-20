<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Entity;
use Domain\Versionable;

/**
 * 障害福祉サービス：居宅介護：算定情報.
 *
 * @property-read int $officeId 事業所ID
 * @property-read \Domain\Common\CarbonRange $period 適用期間
 * @property-read \Domain\Office\HomeHelpServiceSpecifiedOfficeAddition $specifiedOfficeAddition 特定事業所加算
 * @property-read \Domain\Office\DwsTreatmentImprovementAddition $treatmentImprovementAddition 処遇改善加算
 * @property-read \Domain\Office\DwsSpecifiedTreatmentImprovementAddition $specifiedTreatmentImprovementAddition 特定処遇改善加算
 * @property-read \Domain\Office\DwsBaseIncreaseSupportAddition $baseIncreaseSupportAddition ベースアップ等支援加算
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class HomeHelpServiceCalcSpec extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'officeId',
            'period',
            'specifiedOfficeAddition',
            'treatmentImprovementAddition',
            'specifiedTreatmentImprovementAddition',
            'baseIncreaseSupportAddition',
            'isEnabled',
            'version',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'officeId' => true,
            'period' => true,
            'specifiedOfficeAddition' => true,
            'treatmentImprovementAddition' => true,
            'specifiedTreatmentImprovementAddition' => true,
            'baseIncreaseSupportAddition' => true,
            'isEnabled' => true,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

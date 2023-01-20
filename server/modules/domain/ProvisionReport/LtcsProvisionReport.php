<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Entity;

/**
 * 介護保険サービス：予実.
 *
 * @property-read int $userId 利用者ID
 * @property-read int $officeId 事業所ID
 * @property-read int $contractId 契約ID
 * @property-read \Domain\Common\Carbon $providedIn サービス提供年月
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportEntry[] $entries サービス情報
 * @property-read \Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition $specifiedOfficeAddition 特定事業所加算
 * @property-read \Domain\Office\LtcsTreatmentImprovementAddition $treatmentImprovementAddition 処遇改善加算
 * @property-read \Domain\Office\LtcsSpecifiedTreatmentImprovementAddition $specifiedTreatmentImprovementAddition 特定処遇改善加算
 * @property-read \Domain\Office\LtcsBaseIncreaseSupportAddition $baseIncreaseSupportAddition 介護職員等ベースアップ等支援加算
 * @property-read \Domain\Office\LtcsOfficeLocationAddition $locationAddition 地域加算
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportOverScore $plan 超過単位（予定）
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportOverScore $result 超過単位（実績）
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportStatus $status 状態
 * @property-read null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class LtcsProvisionReport extends Entity
{
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'officeId',
            'contractId',
            'providedIn',
            'entries',
            'specifiedOfficeAddition',
            'treatmentImprovementAddition',
            'specifiedTreatmentImprovementAddition',
            'baseIncreaseSupportAddition',
            'locationAddition',
            'plan',
            'result',
            'status',
            'fixedAt',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'userId' => true,
            'officeId' => true,
            'contractId' => true,
            'providedIn' => true,
            'entries' => true,
            'specifiedOfficeAddition' => true,
            'treatmentImprovementAddition' => true,
            'specifiedTreatmentImprovementAddition' => true,
            'baseIncreaseSupportAddition' => true,
            'locationAddition' => true,
            'plan' => true,
            'result' => true,
            'status' => true,
            'fixedAt' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

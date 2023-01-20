<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Entity;

/**
 * 障害福祉サービス：予実.
 *
 * @property-read int $userId 利用者ID
 * @property-read int $officeId 事業所ID
 * @property-read int $contractId 契約ID
 * @property-read \Domain\Common\Carbon $providedIn サービス提供年月
 * @property-read \Domain\ProvisionReport\DwsProvisionReportItem[] $plans 予定
 * @property-read \Domain\ProvisionReport\DwsProvisionReportItem[] $results 実績
 * @property-read \Domain\ProvisionReport\DwsProvisionReportStatus $status 状態
 * @property-read null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsProvisionReport extends Entity
{
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'officeId',
            'contractId',
            'providedIn',
            'plans',
            'results',
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
            'plans' => true,
            'results' => true,
            'status' => true,
            'fixedAt' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

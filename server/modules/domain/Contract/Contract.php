<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Contract;

use Domain\Entity;
use Domain\Versionable;

/**
 * 契約.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read int $userId 利用者ID
 * @property-read int $officeId 事業所ID
 * @property-read \Domain\Common\ServiceSegment $serviceSegment 事業領域
 * @property-read \Domain\Contract\ContractStatus $status 契約状態
 * @property-read null|\Domain\Common\Carbon $contractedOn 契約日
 * @property-read null|\Domain\Common\Carbon $terminatedOn 解約日
 * @property-read \Domain\Contract\ContractPeriod[] $dwsPeriods 障害福祉サービス提供期間
 * @property-read \Domain\Contract\ContractPeriod $ltcsPeriod 介護保険サービス提供期間
 * @property-read \Domain\Billing\LtcsExpiredReason $expiredReason 介護保険サービス中止理由
 * @property-read string $note 備考
 * @property-read int $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class Contract extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    public function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'userId',
            'officeId',
            'serviceSegment',
            'status',
            'contractedOn',
            'terminatedOn',
            'dwsPeriods',
            'ltcsPeriod',
            'expiredReason',
            'note',
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
            'organizationId' => false,
            'userId' => true,
            'officeId' => true,
            'serviceSegment' => true,
            'status' => true,
            'contractedOn' => true,
            'terminatedOn' => true,
            'dwsPeriods' => true,
            'ltcsPeriod' => true,
            'expiredReason' => true,
            'note' => true,
            'isEnabled' => false,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

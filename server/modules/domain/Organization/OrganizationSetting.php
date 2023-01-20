<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Organization;

use Domain\Entity;

/**
 * 事業者別設定.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read string $bankingClientCode 委託者番号
 * @property-read string $bankingClientName 委託者名
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class OrganizationSetting extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'bankingClientCode',
            'bankingClientName',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => false,
            'organizationId' => false,
            'bankingClientCode' => true,
            'bankingClientName' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

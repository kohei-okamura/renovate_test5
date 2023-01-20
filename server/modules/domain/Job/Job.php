<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Job;

use Domain\Entity;

/**
 * 非同期ジョブ.
 *
 * @property-read array|mixed[] $data データ
 * @property-read int $organizationId 事業者ID
 * @property-read int $staffId スタッフID
 * @property-read \Domain\Job\JobStatus $status ジョブ状態
 * @property-read string $token トークン
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class Job extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'staffId',
            'data',
            'status',
            'token',
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
            'staffId' => false,
            'data' => true,
            'status' => true,
            'token' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Entity;

/**
 * 障害福祉サービス：計画：サービス内容.
 *
 * @property-read \Domain\Project\DwsProjectServiceCategory $category サービス区分
 * @property-read string $name 名称
 * @property-read string $displayName 表示名
 * @property-read int $sortOrder 表示順
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class DwsProjectServiceMenu extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'category',
            'name',
            'displayName',
            'sortOrder',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'category' => true,
            'name' => true,
            'displayName' => true,
            'sortOrder' => true,
            'createdAt' => true,
        ];
    }
}

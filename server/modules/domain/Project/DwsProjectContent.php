<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Model;

/**
 * 障害福祉サービス：計画：サービス詳細.
 *
 * @property-read int $menuId サービス内容 ID
 * @property-read null|int $duration 所要時間
 * @property-read string $content サービスの具体的内容
 * @property-read string $memo 留意事項
 */
final class DwsProjectContent extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'menuId',
            'duration',
            'content',
            'memo',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'menuId' => true,
            'duration' => true,
            'content' => true,
            'memo' => true,
        ];
    }
}

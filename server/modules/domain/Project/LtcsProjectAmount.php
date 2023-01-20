<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Model;

/**
 * 介護保険サービス：計画：サービス提供量.
 *
 * @property-read \Domain\Project\LtcsProjectAmountCategory $category サービス区分
 * @property-read int $amount サービス時間
 */
final class LtcsProjectAmount extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'category',
            'amount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'category' => true,
            'amount' => true,
        ];
    }
}

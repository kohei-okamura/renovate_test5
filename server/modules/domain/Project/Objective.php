<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Model;

/**
 * 期間目標.
 *
 * @property-read \Domain\Common\CarbonRange $term 期間
 * @property-read string $text 目標
 */
final class Objective extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'term',
            'text',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'term' => true,
            'text' => true,
        ];
    }
}

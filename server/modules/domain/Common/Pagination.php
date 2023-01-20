<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * ページネーション.
 *
 * @property-read int $count
 * @property-read bool $desc
 * @property-read int $itemsPerPage
 * @property-read int $page
 * @property-read int $pages
 * @property-read string $sortBy
 */
final class Pagination extends Model
{
    /**
     * Return name of attrs.
     *
     * @return array|string[]
     */
    protected function attrs(): array
    {
        return [
            'count',
            'desc',
            'itemsPerPage',
            'page',
            'pages',
            'sortBy',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'count' => true,
            'desc' => true,
            'itemsPerPage' => true,
            'page' => true,
            'pages' => true,
            'sortBy' => true,
        ];
    }
}

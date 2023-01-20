<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Illuminate\Support\LazyCollection;

/**
 * Finder Interface.
 */
interface Finder
{
    public const DEFAULT_ITEMS_PER_PAGE = 10;

    /**
     * Find entities.
     *
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function find(array $filterParams, array $paginationParams): FinderResult;

    /**
     * Find entities via cursor.
     *
     * @param array $filterParams
     * @param array $orderParams ソート順を指定する(sortByは必須)
     * @return \Illuminate\Support\LazyCollection
     */
    public function cursor(array $filterParams, array $orderParams): LazyCollection;
}

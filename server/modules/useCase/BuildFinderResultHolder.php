<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase;

use Domain\Common\Pagination;
use Domain\Finder;
use Domain\FinderResult;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 検索結果を組み立てる.
 */
trait BuildFinderResultHolder
{
    /**
     * 検索結果を組み立てる.
     *
     * @param \Domain\Entity[]|\ScalikePHP\Seq $entities
     * @param array $paginationParams
     * @param string $sortBy
     * @return \Domain\FinderResult
     */
    protected function buildFinderResult(Seq $entities, array $paginationParams, string $sortBy): FinderResult
    {
        if (empty($paginationParams['all'])) {
            $count = $entities->count();
            $desc = $paginationParams['desc'] ?? false;
            $itemsPerPage = $paginationParams['itemsPerPage'] ?? Finder::DEFAULT_ITEMS_PER_PAGE;
            $page = $paginationParams['page'] ?? 1;
            $pages = $count === 0 ? 1 : Math::ceil($count / $itemsPerPage);
            return FinderResult::from(
                $entities->drop($itemsPerPage * ($page - 1))->take($itemsPerPage),
                Pagination::create(compact(
                    'count',
                    'desc',
                    'itemsPerPage',
                    'page',
                    'pages',
                    'sortBy',
                ))
            );
        } else {
            $count = $entities->count();
            return FinderResult::from($entities, Pagination::create([
                'count' => $count,
                'desc' => $paginationParams['desc'] ?? false,
                'itemsPerPage' => $count,
                'page' => 1,
                'pages' => 1,
                'sortBy' => $sortBy,
            ]));
        }
    }
}

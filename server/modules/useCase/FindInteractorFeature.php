<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase;

use Domain\Context\Context;
use Domain\Finder;
use Domain\FinderResult;
use Illuminate\Support\Arr;

/**
 * 検索ユースケース標準実装.
 */
trait FindInteractorFeature
{
    protected Finder $finder;

    /**
     * 検索する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult
    {
        $sortBy = Arr::get($paginationParams, 'sortBy');
        $filter = $this->defaultFilterParams($context) + $filterParams;
        return $this->finder->find(
            $filter,
            ['sortBy' => $sortBy ?: $this->defaultSortBy()] + $paginationParams
        );
    }

    /**
     * デフォルトのフィルタパラメータ.
     *
     * @param \Domain\Context\Context $context
     * @return array
     */
    protected function defaultFilterParams(Context $context): array
    {
        return [];
    }

    /**
     * ソート順のデフォルト値.
     *
     * @return string
     */
    abstract protected function defaultSortBy(): string;
}

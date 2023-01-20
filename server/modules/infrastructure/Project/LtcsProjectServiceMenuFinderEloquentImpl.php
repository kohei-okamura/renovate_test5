<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProjectServiceMenuFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Project\LtcsProjectServiceMenuFinder} Eloquent 実装.
 */
final class LtcsProjectServiceMenuFinderEloquentImpl extends EloquentFinder implements LtcsProjectServiceMenuFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return LtcsProjectServiceMenu::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return LtcsProjectServiceMenu::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        return $query;
    }
}

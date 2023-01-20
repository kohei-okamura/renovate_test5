<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGradeFinder;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\LtcsAreaGrade\LtcsAreaGradeFinder} Eloquent 実装.
 */
final class LtcsAreaGradeFinderEloquentImpl extends EloquentFinder implements LtcsAreaGradeFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return LtcsAreaGrade::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return LtcsAreaGrade::TABLE;
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'code':
                return 'code';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunkFinder;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkFinder} Eloquent 実装.
 */
final class DwsVisitingCareForPwsdChunkFinderEloquentImpl extends EloquentFinder implements DwsVisitingCareForPwsdChunkFinder
{
    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return DwsVisitingCareForPwsdChunk::TABLE;
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsVisitingCareForPwsdChunk::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'category':
                assert($value instanceof DwsServiceCodeCategory);
                return $query->where('category', '=', $value->value());
            case 'providedOn':
                // SQLite を用いるため UNIX タイムスタンプで比較する.
                assert($value instanceof Carbon);
                return $query->where('provided_on', '=', $value->unix());
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return $query;
        }
    }
}

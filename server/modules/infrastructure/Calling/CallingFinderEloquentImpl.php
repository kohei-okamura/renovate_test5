<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\CallingFinder;
use Domain\Common\Range;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Calling\CallingFinder} Eloquent 実装.
 */
final class CallingFinderEloquentImpl extends EloquentFinder implements CallingFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Calling::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'expiredRange':
                assert($value instanceof Range);
                return $query->whereBetween('expired_at', [$value->start, $value->end]);
            case 'response':
                assert(is_bool($value));
                // 第3引数は 'not' なので、true にすると `NOT EXISTS` で動作する
                // response=trueは、calling_response が存在していることを filter したいので、`!$value` と渡す
                // see: https://laravel.com/api/7.x/Illuminate/Database/Query/Builder.html#method_whereExists
                return $query->whereExists(
                    function (Builder $q): void {
                        $q->from('calling_response')->whereRaw('calling_response.calling_id = calling.id');
                    },
                    'and',
                    !$value
                );
            default:
                return $query;
        }
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'date':
                return 'expired_at';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}

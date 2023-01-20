<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * リレーション関連の処理.
 *
 * @mixin \Eloquent
 */
trait RelationSupport
{
    /**
     * 属性テーブルとのリレーションを定義する.
     *
     * @param string $related
     * @param null|string $foreignKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    protected function hasAttribute(string $related, string $foreignKey = null): HasOne
    {
        return $this->hasOne($related, $foreignKey)->whereExists(function (QueryBuilder $query): void {
            $t = $this->table . '_to_attr';
            $u = $this->table . '_attr';
            $id = $this->table . '_attr_id';
            $query->selectRaw('1')->from($t)->whereRaw("{$id} = {$u}.id");
        });
    }

    /**
     * 列挙型を用いるリレーション等を変換してから返す.
     *
     * @param string $relationKey
     * @param \Closure $f
     * @return array
     * @codeCoverageIgnore リレーションの定義のため
     */
    protected function mapRelation(string $relationKey, Closure $f): array
    {
        $xs = $this->getRelationValue($relationKey);
        if ($xs === null) {
            return [];
        } else {
            assert($xs instanceof Collection);
            return $xs->map($f)->toArray();
        }
    }

    /**
     * リレーション等をソートし、変換してから返す.
     *
     * @param string $relationKey
     * @param \Closure $f
     * @param string $sortColumn
     * @return array
     * @codeCoverageIgnore リレーションの定義のため
     */
    protected function mapSortRelation(string $relationKey, string $sortColumn, Closure $f): array
    {
        $xs = $this->getRelationValue($relationKey);
        if ($xs === null) {
            return [];
        } else {
            assert($xs instanceof Collection);
            return $xs->sortBy($sortColumn)->map($f)->toArray();
        }
    }
}

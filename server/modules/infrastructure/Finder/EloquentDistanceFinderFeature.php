<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Finder;

use Domain\Common\Distance;
use Domain\Common\Location;
use Domain\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Domainable;
use Lib\Exceptions\InvalidArgumentException;

/**
 * Trait EloquentDistanceFinderFeature.
 */
trait EloquentDistanceFinderFeature
{
    /**
     * SRID.
     *
     * @link https://qiita.com/boiledorange73/items/b98d3d1ef3abf7299aba
     */
    private int $srid = 4326;

    /**
     * 距離情報検索用のパラメータが存在することを担保する.
     *
     * @param array $filterParams
     * @return void
     */
    protected function ensureDistanceFinder(array $filterParams): void
    {
        $hasLocationParams = isset($filterParams['location'], $filterParams['range'])
            && $filterParams['location'] instanceof Location
            && is_numeric($filterParams['range']);
        if (!$hasLocationParams) {
            throw new InvalidArgumentException('Invalid argument');
        }
    }

    /**
     * 距離情報の検索条件をセットする.
     *
     * @param EloquentBuilder $query
     * @param array $filterParams
     * @return void
     */
    protected function setDistanceCondition(EloquentBuilder $query, array $filterParams): void
    {
        $location = $filterParams['location'];
        $range = (int)$filterParams['range'];
        $this->setDistanceConditionInner($query, $location, $range);
    }

    /**
     * 距離情報の検索条件をセットする.
     *
     * @param EloquentBuilder $query
     * @param \Domain\Common\Location $location
     * @param int $range
     * @return void
     */
    protected function setDistanceConditionInner(EloquentBuilder $query, Location $location, int $range): void
    {
        $table = $this->baseTableName();
        $expr = "ST_Distance_Sphere(ST_GeomFromText(?, ?), {$table}_attr.location)";
        $bindValues = [sprintf('POINT(%f %f)', $location->lat, $location->lng), $this->srid];
        $query
            ->select($table . '.*')
            ->selectRaw($expr . ' AS distance ')
            ->addBinding($bindValues, 'select')
            ->whereRaw($expr . ' <= ' . $range)
            ->addBinding($bindValues, 'where');
    }

    /**
     * 検索結果をパースする.
     *
     * @param \Infrastructure\Domainable|\Infrastructure\Model $x
     * @return \Domain\Model
     * @see \Infrastructure\Finder\EloquentFinder::parseResult()
     * @noinspection PhpUnused
     */
    protected function parseResult(Domainable $x): Model
    {
        return Distance::create([
            'distance' => $x->getAttribute('distance'),
            'destination' => $x->toDomain(),
        ]);
    }

    /**
     * クエリビルダーにソート順を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param bool $desc
     * @return EloquentBuilder
     * @see \Infrastructure\Finder\EloquentFinder::setSortBy()
     * @noinspection PhpUnused
     */
    protected function setSortBy(EloquentBuilder $query, string $sortBy, bool $desc): EloquentBuilder
    {
        switch ($sortBy) {
            case 'distance':
                $direction = $desc ? 'desc' : 'asc';
                return $query->orderBy('distance', $direction);
            default:
                return parent::setSortBy($query, $sortBy, $desc);
        }
    }
}

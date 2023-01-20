<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Location;
use Illuminate\Database\Eloquent\Builder;
use Lib\Exceptions\LogicException;

/**
 * {@link \Domain\Common\Location} Holder.
 *
 * @property null|float $location_lat 緯度
 * @property null|float $location_lng 軽度
 * @property-read \Domain\Common\Location $location 位置情報
 * @method static \Illuminate\Database\Eloquent\Builder|static whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereLng($value)
 * @mixin \Eloquent
 */
trait LocationHolder
{
    /**
     * SRID.
     *
     * @link https://qiita.com/boiledorange73/items/b98d3d1ef3abf7299aba
     */
    private int $srid = 4326;

    /**
     * LocationHolder のboot時にグローバルスコープを設定する.
     *
     * @link https://qiita.com/niisan-tokyo/items/d3be588b53df8fa0278c
     * @return void
     * @noinspection PhpUnused
     */
    protected static function bootLocationHolder(): void
    {
        static::addGlobalScope('get_and_parse_of_point_value', function (Builder $builder): void {
            $builder->select('*')->selectRaw('ST_AsText(location) AS location');
        });
    }

    /**
     * Get mutator for location attribute.
     *
     * @param string $value
     * @return \Domain\Common\Location
     * @noinspection PhpUnused
     */
    protected function getLocationAttribute(string $value): Location
    {
        preg_match_all('/\APOINT\((-?[0-9|.]+) (-?[0-9|.]+)\)\z/i', $value, $matches);

        if (!isset($matches[1][0]) || !isset($matches[2][0])) {
            //  DBの定義型誤り時のFail-safeため
            throw new LogicException('Unable to get location points'); // @codeCoverageIgnore
        }

        return Location::create([
            'lat' => (float)$matches[1][0],
            'lng' => (float)$matches[2][0],
        ]);
    }

    /**
     * Set mutator for location attribute.
     *
     * @param \Domain\Common\Location $location
     * @return void
     * @noinspection PhpUnused
     */
    protected function setLocationAttribute(Location $location): void
    {
        $point = sprintf('POINT(%f %f)', $location->lat, $location->lng);
        $this->attributes['location'] = $this->raw("ST_GeomFromText('{$point}', {$this->srid})");
    }
}

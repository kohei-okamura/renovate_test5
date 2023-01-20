<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGrade as DomainDwsAreaGrade;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス地域区分 Eloquent モデル.
 *
 * @property int $id 障害地域区分ID
 * @property string $code 障害地域区分コード
 * @property string $name 障害地域区分名
 * @method static \Illuminate\Database\Eloquent\Builder|DwsAreaGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsAreaGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsAreaGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsAreaGrade whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsAreaGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsAreaGrade whereName($value)
 * @mixin \Eloquent
 */
final class DwsAreaGrade extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_area_grade';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'code',
        'name',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsAreaGrade
    {
        return DomainDwsAreaGrade::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGrade $domain
     * @return \Infrastructure\DwsAreaGrade\DwsAreaGrade
     */
    public static function fromDomain(DomainDwsAreaGrade $domain): self
    {
        $keys = ['id', 'code', 'name'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

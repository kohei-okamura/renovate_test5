<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGrade as DomainLtcsAreaGrade;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介保地域区分 Eloquent モデル.
 *
 * @property int $id 介保地域区分ID
 * @property string $code 介保地域区分コード
 * @property string $name 介保地域区分名
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsAreaGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsAreaGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsAreaGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsAreaGrade whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsAreaGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsAreaGrade whereName($value)
 * @mixin \Eloquent
 */
final class LtcsAreaGrade extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_area_grade';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'code',
        'name',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsAreaGrade
    {
        return DomainLtcsAreaGrade::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGrade $domain
     * @return \Infrastructure\LtcsAreaGrade\LtcsAreaGrade
     */
    public static function fromDomain(DomainLtcsAreaGrade $domain): self
    {
        $keys = ['id', 'code', 'name'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

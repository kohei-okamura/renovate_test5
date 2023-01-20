<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\DwsCertification\DwsType;
use Infrastructure\Model;

/**
 * 障害福祉サービス受給者証属性・障害種別中間テーブル Eloquent モデル.
 *
 * @property int $dws_certification_attr_id
 * @property int $dws_type
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttrDwsType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttrDwsType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttrDwsType query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttrDwsType whereStaffAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttrDwsType whereCertification($value)
 * @mixin \Eloquent
 */
final class DwsCertificationAttrDwsType extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_certification_attr_dws_type';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'dws_certification_attr_id',
        'dws_type',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsCertification\DwsType $dwsType
     * @return \Infrastructure\DwsCertification\DwsCertificationAttrDwsType
     */
    public static function fromDomain(DwsType $dwsType): self
    {
        return self::newModelInstance(['dws_type' => $dwsType]);
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\DwsCertification\DwsCertificationGrant as DomainDwsCertificationGrant;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス受給者証 支給量 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス受給者証 支給量ID
 * @property int $dws_certification_attr_id 障害福祉サービス受給者証属性ID
 * @property \Domain\DwsCertification\DwsCertificationServiceType $dws_certification_service_type 障害福祉サービス受給者証 サービス種別
 * @property string $granted_amount 支給量等
 * @property \Domain\Common\Carbon $activated_on 認定の有効期間（開始）
 * @property \Domain\Common\Carbon $deactivated_on 認定の有効期間（終了）
 * @property int $sort_order 表示順
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereActivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereDeactivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereDwsCertificationAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereDwsCertificationServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereGrantedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationGrant whereSortOrder($value)
 * @mixin \Eloquent
 */
final class DwsCertificationGrant extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_certification_grant';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_certification_attr_id',
        'dws_certification_service_type',
        'granted_amount',
        'activated_on',
        'deactivated_on',
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'activated_on' => 'date',
        'deactivated_on' => 'date',
        'dws_certification_service_type' => CastsDwsCertificationServiceType::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsCertificationGrant
    {
        return DomainDwsCertificationGrant::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsCertification\DwsCertificationGrant $domain
     * @param array $values
     * @return \Infrastructure\DwsCertification\DwsCertificationGrant
     */
    public static function fromDomain(DomainDwsCertificationGrant $domain, array $values): self
    {
        $keys = [
            'dws_certification_service_type',
            'granted_amount',
            'activated_on',
            'deactivated_on',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs + $values);
    }
}

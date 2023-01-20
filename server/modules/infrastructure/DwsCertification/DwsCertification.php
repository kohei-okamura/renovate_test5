<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\DwsCertification\DwsCertification as DomainDwsCertification;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス受給者証 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス受給者証ID
 * @property int $user_id 利用者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\DwsCertification\DwsCertificationAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertification query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertification whereUserId($value)
 * @mixin \Eloquent
 */
final class DwsCertification extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_certification';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'attr',
        'attr.dwsTypes',
        'attr.grants',
        'attr.agreements',
    ];

    /**
     * HasOne: {@link \Infrastructure\DwsCertification\DwsCertificationAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(DwsCertificationAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsCertification
    {
        return DomainDwsCertification::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsCertification\DwsCertification $domain
     * @return \Infrastructure\DwsCertification\DwsCertification
     */
    public static function fromDomain(DomainDwsCertification $domain): self
    {
        $keys = ['id', 'user_id', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

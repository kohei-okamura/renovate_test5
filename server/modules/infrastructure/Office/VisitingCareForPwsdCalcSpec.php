<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\VisitingCareForPwsdCalcSpec as DomainVisitingCareForPwsdCalcSpec;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業所算定情報（障害・重度訪問介護） Eloquent モデル.
 *
 * @property int $id ID
 * @property int $officeId 事業所ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Office\VisitingCareForPwsdCalcSpecAttr $attr
 */
class VisitingCareForPwsdCalcSpec extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'visiting_care_for_pwsd_calc_spec';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'office_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\Office\VisitingCareForPwsdCalcSpecAttr}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(VisitingCareForPwsdCalcSpecAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain()
    {
        return DomainVisitingCareForPwsdCalcSpec::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $domain
     * @return \Infrastructure\Office\VisitingCareForPwsdCalcSpec
     */
    public static function fromDomain(DomainVisitingCareForPwsdCalcSpec $domain): self
    {
        $keys = ['id', 'office_id', 'created_at'];
        $values = self::getDomainvalues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

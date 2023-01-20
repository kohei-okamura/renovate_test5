<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeHelpServiceCalcSpec as DomainHomeHelpServiceCalcSpec;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業所算定情報（障害・居宅介護） Eloquent モデル.
 *
 * @property int $id ID
 * @property int $officeId 事業所ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Office\HomeHelpServiceCalcSpecAttr $attr
 */
class HomeHelpServiceCalcSpec extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'home_help_service_calc_spec';

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
     * HasOne: {@link \Infrastructure\Office\HomeHelpServiceCalcSpecAttr}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(HomeHelpServiceCalcSpecAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain()
    {
        return DomainHomeHelpServiceCalcSpec::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\HomeHelpServiceCalcSpec $domain
     * @return \Infrastructure\Office\HomeHelpServiceCalcSpec
     */
    public static function fromDomain(DomainHomeHelpServiceCalcSpec $domain): self
    {
        $keys = ['id', 'office_id', 'created_at'];
        $values = self::getDomainvalues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

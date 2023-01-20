<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCard as DomainLtcsInsCard;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険被保険者証 Eloquent モデル.
 *
 * @property int $id 介護保険被保険者証ID
 * @property int $user_id 利用者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\LtcsInsCard\LtcsInsCardAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCard whereUserId($value)
 * @mixin \Eloquent
 */
final class LtcsInsCard extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_ins_card';

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
    protected $with = ['attr', 'attr.maxBenefitQuotas'];

    /**
     * HasOne: {@link \Infrastructure\LtcsInsCard\LtcsInsCardAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(LtcsInsCardAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsInsCard
    {
        return DomainLtcsInsCard::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard $domain
     * @return \Infrastructure\LtcsInsCard\LtcsInsCard
     */
    public static function fromDomain(DomainLtcsInsCard $domain): self
    {
        $keys = ['id', 'user_id', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

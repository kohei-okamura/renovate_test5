<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Domain\Organization\Organization as DomainOrganization;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業者 Eloquent モデル.
 *
 * @property int $id 事業者ID
 * @property string $code 事業者コード
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Organization\OrganizationAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @mixin \Eloquent
 */
final class Organization extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'organization';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'code',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\Organization\OrganizationAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(OrganizationAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOrganization
    {
        return DomainOrganization::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Organization\Organization $domain
     * @return \Infrastructure\Organization\Organization
     */
    public static function fromDomain(DomainOrganization $domain): self
    {
        $keys = ['id', 'code', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

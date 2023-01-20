<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Contract;

use Domain\Contract\Contract as DomainContract;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Office\BelongsToOffice;
use Infrastructure\Organization\BelongsToOrganization;
use Infrastructure\User\BelongsToUser;

/**
 * 契約 Eloquent モデル.
 *
 * @property int $id 契約ID
 * @property int $user_id 利用者ID
 * @property \Illuminate\Support\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Contract\ContractAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @mixin \Eloquent
 */
final class Contract extends Model implements Domainable
{
    use BelongsToOffice;
    use BelongsToOrganization;
    use BelongsToUser;

    /**
     * テーブル名.
     */
    public const TABLE = 'contract';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'organization_id',
        'user_id',
        'office_id',
        'created_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr', 'attr.dwsPeriods'];

    /**
     * HasOne: {@link \Infrastructure\Contract\ContractAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(ContractAttr::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Contract\Contract $domain
     * @return \Infrastructure\Contract\Contract
     */
    public static function fromDomain(DomainContract $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainContract
    {
        return DomainContract::create($this->toDomainValues() + $this->attr->toDomainValues());
    }
}

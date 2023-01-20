<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\User as DomainUser;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\BankAccount\BelongsToBankAccount;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * 利用者 Eloquent モデル.
 *
 * @property int $id 利用者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\User\UserAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @mixin \Eloquent
 */
final class User extends Model implements Domainable
{
    use BelongsToBankAccount;
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'user';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'bank_account_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\User\UserAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(UserAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainUser
    {
        return DomainUser::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\User $domain
     * @return \Infrastructure\User\User
     */
    public static function fromDomain(DomainUser $domain): self
    {
        $keys = ['id', 'organization_id', 'bank_account_id', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

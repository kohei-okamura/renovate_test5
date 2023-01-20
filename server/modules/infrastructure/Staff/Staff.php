<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\Staff as DomainStaff;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\BankAccount\BelongsToBankAccount;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * スタッフ Eloquent モデル.
 *
 * @property int $id スタッフID
 * @property int $organization_id 事業者ID
 * @property int $bank_account_id 銀行口座ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Staff\StaffAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff query()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereId($value)
 * @mixin \Eloquent
 */
final class Staff extends Model implements Domainable
{
    use BelongsToBankAccount;
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'staff';

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
    protected $with = [
        'attr',
        'attr.roles',
    ];

    /**
     * HasOne: {@link \Infrastructure\Staff\StaffAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(StaffAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainStaff
    {
        return DomainStaff::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\Staff $domain
     * @return \Infrastructure\Staff\Staff
     */
    public static function fromDomain(DomainStaff $domain): self
    {
        $keys = ['id', 'organization_id', 'bank_account_id', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\StaffRememberToken as DomainStaffRememberToken;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * スタッフリメンバートークン Eloquent モデル.
 *
 * @property int $id スタッフリメンバートークンID
 * @property string $token トークン
 * @property \Illuminate\Support\Carbon $expired_at 有効期限
 * @property \Illuminate\Support\Carbon $created_at 登録日時
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffRememberToken whereRememberToken($value)
 * @mixin \Eloquent
 */
final class StaffRememberToken extends Model implements Domainable
{
    use BelongsToStaff;

    /**
     * テーブル名.
     */
    public const TABLE = 'staff_remember_token';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'staff_id',
        'token',
        'expired_at',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainStaffRememberToken
    {
        return DomainStaffRememberToken::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\StaffRememberToken $domain
     * @return \Infrastructure\Staff\StaffRememberToken
     */
    public static function fromDomain(DomainStaffRememberToken $domain): self
    {
        $keys = ['id', 'staff_id', 'token', 'expired_at', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

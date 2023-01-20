<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Common\StructuredName;
use Domain\Staff\StaffPasswordReset as DomainStaffPasswordReset;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * スタッフパスワード再設定 Eloquent モデル.
 *
 * @property int $id スタッフパスワード再設定ID
 * @property \Domain\Common\StructuredName $name スタッフ名
 * @property string $email メールアドレス
 * @property string $token トークン
 * @property \Domain\Common\Carbon $expired_at 有効期限
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @method static \Illuminate\Database\Eloquent\Builder|StaffPasswordReset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffPasswordReset whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffPasswordReset whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffPasswordReset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffPasswordReset whereToken($value)
 * @mixin \Eloquent
 */
final class StaffPasswordReset extends Model implements Domainable
{
    use BelongsToStaff;

    /**
     * テーブル名.
     */
    public const TABLE = 'staff_password_reset';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'staff_id',
        'email',
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
    protected $with = ['staff'];

    /** {@inheritdoc} */
    public function toDomain(): DomainStaffPasswordReset
    {
        return DomainStaffPasswordReset::create(
            $this->toDomainValues() + $this->only('name') + ['staff' => $this->staff->toDomain()]
        );
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\StaffPasswordReset $domain
     * @return \Infrastructure\Staff\StaffPasswordReset
     */
    public static function fromDomain(DomainStaffPasswordReset $domain): self
    {
        $keys = ['id', 'staff_id', 'email', 'token', 'expired_at', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /**
     * Get mutator for name attribute.
     *
     * @return \Domain\Common\StructuredName
     * @noinspection PhpUnused
     */
    public function getNameAttribute(): StructuredName
    {
        return $this->staff->attr->name;
    }
}

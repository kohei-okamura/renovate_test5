<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Common\StructuredName;
use Domain\Staff\StaffEmailVerification as DomainStaffEmailVerification;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * スタッフメールアドレス確認 Eloquent モデル.
 *
 * @property int $id スタッフメールアドレス検証ID
 * @property \Domain\Common\StructuredName $name スタッフ名
 * @property string $email メールアドレス
 * @property string $token トークン
 * @property \Domain\Common\Carbon $expired_at 有効期限
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffEmailVerification whereToken($value)
 * @mixin \Eloquent
 */
final class StaffEmailVerification extends Model implements Domainable
{
    use BelongsToStaff;

    /**
     * テーブル名.
     */
    public const TABLE = 'staff_email_verification';

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
    public function toDomain(): DomainStaffEmailVerification
    {
        return DomainStaffEmailVerification::create(
            $this->only('name') + $this->toDomainValues()
        );
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\StaffEmailVerification $domain
     * @return \Infrastructure\Staff\StaffEmailVerification
     */
    public static function fromDomain(DomainStaffEmailVerification $domain): self
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

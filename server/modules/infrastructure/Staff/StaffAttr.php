<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\Certification;
use Domain\Staff\Staff as DomainStaff;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\AddrHolder;
use Infrastructure\Common\CastsSex;
use Infrastructure\Common\LocationHolder;
use Infrastructure\Common\NameHolder;
use Infrastructure\Common\PasswordHolder;
use Infrastructure\Model;
use Infrastructure\Office\Office;
use Infrastructure\Office\OfficeGroup;
use Infrastructure\Role\Role;

/**
 * スタッフ属性 Eloquent モデル.
 *
 * @property int $id スタッフ属性ID
 * @property int $staff_id スタッフID
 * @property string $employee_number 社員番号
 * @property \Domain\Common\Carbon $birthday 生年月日
 * @property string $tel 電話番号
 * @property string $fax 電話番号
 * @property string $email メールアドレス
 * @property \Domain\Common\Password $password パスワード
 * @property string $password_hash パスワードハッシュ
 * @property bool $is_verified メールアドレス検証済みフラグ
 * @property-read int $status 状態
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Domain\Staff\Certification[]|\Illuminate\Database\Eloquent\Collection $certifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Office\Office[] $offices
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Role\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr query()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereEmployeeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr wherePasswordHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttr whereVersion($value)
 * @mixin \Eloquent
 */
final class StaffAttr extends Model
{
    use AddrHolder;
    use BelongsToStaff;
    use LocationHolder;
    use NameHolder;
    use PasswordHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'staff_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'staff_id',
        'employee_number',
        'name',
        'sex',
        'birthday',
        'addr',
        'location',
        'tel',
        'fax',
        'email',
        'password',
        'is_verified',
        'status',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_verified' => 'boolean',
        'is_enabled' => 'boolean',
        'birthday' => 'date',
        'updated_at' => 'datetime',
        'sex' => CastsSex::class,
        'status' => CastsStaffStatus::class,
    ];

    /**
     * HasMany: {@link \Infrastructure\Staff\StaffAttrCertification}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @noinspection PhpUnused
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(StaffAttrCertification::class);
    }

    /**
     * BelongsToMany: {@link \Infrastructure\Office\Office}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class, 'staff_attr_to_office');
    }

    /**
     * BelongsToMany: {@link \Infrastructure\Office\OfficeGroup}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * @codeCoverageIgnore リレーション定義のため
     */
    public function officeGroups(): BelongsToMany
    {
        return $this->belongsToMany(OfficeGroup::class, 'staff_attr_to_office_group');
    }

    /**
     * BelongsToMany: {@link \Infrastructure\Role\Role}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'staff_attr_to_role');
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'name',
            'addr',
            'location',
            'password',
            'certifications',
        ];
        $values = [
            'officeIds' => $this->offices()->allRelatedIds()->toArray(),
            'officeGroupIds' => $this->officeGroups()->allRelatedIds()->toArray(),
            'roleIds' => $this->roles()->allRelatedIds()->toArray(),
        ];
        return $this->only($hasGetMutatorAttrs) + $values + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\Staff $domain
     * @return \Infrastructure\Staff\StaffAttr
     */
    public static function fromDomain(DomainStaff $domain): self
    {
        $keys = [
            'employee_number',
            'name',
            'addr',
            'location',
            'sex',
            'birthday',
            'tel',
            'fax',
            'email',
            'password',
            'is_verified',
            'status',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /**
     * Get mutator for certifications attribute.
     *
     * @return array|\Domain\Staff\Certification[]
     * @noinspection PhpUnused
     */
    protected function getCertificationsAttribute(): array
    {
        return $this->mapRelation(
            'certifications',
            fn (StaffAttrCertification $x) => Certification::from($x->certification)
        );
    }
}

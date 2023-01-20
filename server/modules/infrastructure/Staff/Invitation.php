<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\Invitation as DomainInvitation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Office\Office;
use Infrastructure\Office\OfficeGroup;
use Infrastructure\Role\Role;

/**
 * 招待 Eloquent モデル.
 *
 * @property int $id 招待ID
 * @property null|int $staff_id スタッフID
 * @property string $email メールアドレス
 * @property string $token トークン
 * @property \Domain\Common\Carbon $expired_at 有効期限
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Role\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Office\Office[] $offices
 */
final class Invitation extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'invitation';

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

    /**
     * BelongsToMany: {@link \Infrastructure\Role\Role}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'invitation_to_role');
    }

    /**
     * BelongsToMany: {@link \Infrastructure\Office\Office}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class, 'invitation_to_office');
    }

    /**
     * BelongsToMany: {@link \Infrastructure\Office\OfficeGroup}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function officeGroups(): BelongsToMany
    {
        return $this->belongsToMany(OfficeGroup::class, 'invitation_to_office_group');
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainInvitation
    {
        $roleIds = $this->roles()->allRelatedIds()->toArray();
        $officeIds = $this->offices()->allRelatedIds()->toArray();
        $officeGroupIds = $this->officeGroups()->allRelatedIds()->toArray();
        return DomainInvitation::create($this->toDomainValues() + compact(
            'roleIds',
            'officeIds',
            'officeGroupIds'
        ));
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\Invitation $domain
     * @return \Infrastructure\Staff\Invitation
     */
    public static function fromDomain(DomainInvitation $domain): self
    {
        $keys = [
            'id',
            'staff_id',
            'email',
            'token',
            'expired_at',
            'created_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

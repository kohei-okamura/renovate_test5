<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Role;

use Domain\Role\Role as DomainRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Permission\SyncPermission;

/**
 * ロール Eloquent モデル.
 *
 * @property int $id ロールID
 * @property string $name ロール名
 * @property int $sort_order 表示順
 * @property \Domain\Role\RoleScope $scope 権限範囲
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Domain\Permission\Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class Role extends Model implements Domainable
{
    use SyncPermission;

    /**
     * テーブル名.
     */
    public const TABLE = 'role';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'name',
        'is_system_admin',
        'scope',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_system_admin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'scope' => CastsRoleScope::class,
    ];

    /** {@inheritdoc} */
    protected $with = ['permissions'];

    /**
     * HasMany: {@link \Infrastructure\Role\RolePermission}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainRole
    {
        return DomainRole::create($this->toDomainValues());
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        return $this->only(['permissions']) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Role\Role $domain
     * @return \Infrastructure\Role\Role
     */
    public static function fromDomain(DomainRole $domain): self
    {
        $keys = ['id', 'organization_id', 'name', 'is_system_admin', 'scope', 'sort_order', 'created_at', 'updated_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /**
     * Get mutator for permissions attribute.
     *
     * @return array|\Domain\Permission\Permission[]
     * @noinspection PhpUnused
     */
    protected function getPermissionsAttribute(): array
    {
        return $this->mapRelation('permissions', fn (RolePermission $x) => $x->permission);
    }
}

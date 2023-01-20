<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Domain\Permission\PermissionGroup as DomainPermissionGroup;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 権限グループ Eloquent モデル.
 *
 * @property int $id 権限グループID
 * @property mixed $code 権限グループコード
 * @property string $name 権限グループ名
 * @property string $display_name 表示名
 * @property int $sort_order 表示順
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Domain\Permission\Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroup whereSortOrder($value)
 * @mixin \Eloquent
 */
final class PermissionGroup extends Model implements Domainable
{
    use SyncPermission;

    /**
     * テーブル名.
     */
    public const TABLE = 'permission_group';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'code',
        'name',
        'display_name',
        'sort_order',
        'created_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'permissions',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['permissions'];

    /**
     * HasMany: {@link \Infrastructure\Permission\PermissionGroupPermission}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionGroupPermission::class);
    }

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Permission\PermissionGroup $domain
     * @return static
     */
    public static function fromDomain(DomainPermissionGroup $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainPermissionGroup
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainPermissionGroup::create($attrs);
    }

    /**
     * Get mutator for permissions attribute.
     *
     * @return array|\Domain\Permission\Permission[]
     * @noinspection PhpUnused
     */
    protected function getPermissionsAttribute(): array
    {
        return $this->mapRelation('permissions', fn (PermissionGroupPermission $x) => $x->permission);
    }
}

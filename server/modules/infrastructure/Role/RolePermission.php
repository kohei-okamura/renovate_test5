<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Role;

use Infrastructure\Model;
use Infrastructure\Permission\CastsPermission;

/**
 * ロール権限 Eloquent モデル.
 *
 * @property int $role_id ロールID
 * @method static \Illuminate\Database\Eloquent\Builder|RolePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RolePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RolePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|RolePermission wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RolePermission whereRoleId($value)
 * @mixin \Eloquent
 */
final class RolePermission extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'role_permission';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'role_id',
        'permission',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'permission' => CastsPermission::class,
    ];
}

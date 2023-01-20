<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Infrastructure\Model;

/**
 * 権限グループに属する権限 Eloquent モデル.
 *
 * @property int $permission_group_id 権限グループID
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroupPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroupPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroupPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroupPermission wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionGroupPermission wherePermissionGroupId($value)
 * @mixin \Eloquent
 */
final class PermissionGroupPermission extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'permission_group_permission';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'permission_group_id',
        'permission',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'permission' => CastsPermission::class,
    ];
}

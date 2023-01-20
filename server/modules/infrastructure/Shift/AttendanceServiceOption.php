<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Infrastructure\Model;

/**
 * サービスオプション（勤務シフト・勤務実績） Eloquent モデル.
 *
 * @property int $attendance_id 勤務実績ID
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceServiceOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceServiceOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceServiceOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceServiceOption wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceServiceOption whereRoleId($value)
 * @mixin \Eloquent
 */
final class AttendanceServiceOption extends Model implements ServiceOptionProvider
{
    use ServiceOptionHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'attendance_service_option';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'attendance_id',
        'service_option',
    ];
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Infrastructure\Model;

/**
 * 勤務シフトサービスオプション（勤務シフト・勤務実績） Eloquent モデル.
 *
 * @property int $shift_id 勤務シフトID
 * @property int $service_option サービスオプション
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftServiceOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftServiceOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftServiceOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftServiceOption wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftServiceOption whereRoleId($value)
 * @mixin \Eloquent
 */
final class ShiftServiceOption extends Model implements ServiceOptionProvider
{
    use ServiceOptionHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'shift_service_option';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'shift_id',
        'service_option',
    ];
}

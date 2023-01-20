<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Assignee as DomainAssignee;
use Infrastructure\Model;

/**
 * 勤務実績担当スタッフ Eloquent モデル.
 *
 * @property int $id 勤務実績担当スタッフID
 * @property int $attendance_id 勤務実績ID
 * @property int $sort_order インデックス
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereName($value)
 * @mixin \Eloquent
 */
final class AttendanceAssignee extends Model implements Assignee
{
    /**
     * テーブル名.
     */
    public const TABLE = 'attendance_assignee';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'attendance_id',
        'sort_order',
        'staff_id',
        'is_training',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_training' => 'boolean',
    ];

    /**
     * 担当スタッフ Domain モデルから Eloquent モデルへ変換する.
     *
     * @param int $attendanceId
     * @param array $assignees
     * @return array|static[]
     */
    public static function domainAssigneesToAttendanceAssignees(int $attendanceId, array $assignees): array
    {
        return array_map(
            fn (DomainAssignee $domainAssignee, int $index): AttendanceAssignee => AttendanceAssignee::fromDomain(
                $domainAssignee,
                [
                    'attendance_id' => $attendanceId,
                    'sort_order' => $index,
                ]
            ),
            $assignees,
            array_keys($assignees)
        );
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Shift\Assignee $domain
     * @param array $additional
     * @return static
     */
    public static function fromDomain(DomainAssignee $domain, array $additional): self
    {
        $keys = ['staff_id', 'is_training'];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $values)->fill($values);
    }
}

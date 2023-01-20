<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Assignee as DomainAssignee;
use Infrastructure\Model;

/**
 * 勤務シフト担当スタッフ Eloquent モデル.
 *
 * @property int $id 勤務シフト担当スタッフID
 * @property int $shift_id 勤務シフトID
 * @property int $sort_order インデックス
 * @property int $staff_id スタッフID
 * @property bool $is_training 研修フラグ
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereName($value)
 * @mixin \Eloquent
 */
final class ShiftAssignee extends Model implements Assignee
{
    /**
     * テーブル名.
     */
    public const TABLE = 'shift_assignee';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'shift_id',
        'sort_order',
        'staff_id',
        'is_training',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_training' => 'boolean',
    ];

    /**
     * 勤務シフト担当スタッフ Domain モデルから Eloquent モデルへ変換する.
     *
     * @param int $shiftId
     * @param array $assignees
     * @return \Infrastructure\Shift\ShiftAssignee[]
     */
    public static function domainAssigneesToShiftAssignees(int $shiftId, array $assignees): array
    {
        return array_map(
            fn (DomainAssignee $domainAssignee, int $index): ShiftAssignee => ShiftAssignee::fromDomain(
                $domainAssignee,
                [
                    'shift_id' => $shiftId,
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
     * @return \Infrastructure\Shift\ShiftAssignee
     */
    public static function fromDomain(DomainAssignee $domain, array $additional): self
    {
        $keys = ['staff_id', 'is_training'];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $values)->fill($values);
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Duration as DomainDuration;
use Infrastructure\Model;

/**
 * 勤務シフト勤務時間.
 *
 * @property int $id 勤務シフト勤務時間ID
 * @property int $shift_id 勤務シフトID
 * @property-read \Domain\Shift\Activity $activity 勤務内容
 * @property-read \Domain\Shift\Duration $duration 勤務時間
 * @mixin \Eloquent
 */
final class ShiftDuration extends Model implements Duration
{
    /**
     * テーブル名.
     */
    public const TABLE = 'shift_duration';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'shift_id',
        'activity',
        'duration',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'activity' => CastsActivity::class,
    ];

    /**
     * 勤務シフト勤務時間 Domain モデルから Eloquent モデルへ変換する.
     *
     * @param int $shiftId
     * @param array $durations
     * @return \Infrastructure\Shift\ShiftDuration[]
     */
    public static function domainDurationsToShiftDurations(int $shiftId, array $durations): array
    {
        return array_map(
            fn (DomainDuration $x): ShiftDuration => ShiftDuration::fromDomain($x, $shiftId),
            $durations
        );
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Shift\Duration $domain
     * @param int $shiftId
     * @return \Infrastructure\Shift\ShiftDuration
     */
    public static function fromDomain(DomainDuration $domain, int $shiftId): self
    {
        $values = self::getDomainValues($domain, ['activity', 'duration']);
        $attributes = [
            'shift_id' => $shiftId,
        ];
        return self::firstOrNew($attributes)->fill($values);
    }
}

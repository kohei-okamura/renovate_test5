<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Duration as DomainDuration;
use Infrastructure\Model;

/**
 * 勤務実績勤務時間.
 *
 * @property int $id 勤務実績勤務時間ID
 * @property int $attendance_id 勤務実績ID
 * @property-read \Domain\Shift\Activity $activity 勤務内容
 * @property-read \Domain\Shift\Duration $duration 勤務時間
 * @mixin \Eloquent
 */
final class AttendanceDuration extends Model implements Duration
{
    /**
     * テーブル名.
     */
    public const TABLE = 'attendance_duration';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'attendance_id',
        'activity',
        'duration',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'activity' => CastsActivity::class,
    ];

    /**
     * 勤務時間 Domain モデルから Eloquent モデルへ変換する.
     *
     * @param int $attendanceId
     * @param array $durations
     * @return array|static[]
     */
    public static function domainDurationsToAttendanceDurations(int $attendanceId, array $durations): array
    {
        return array_map(
            fn (DomainDuration $x): AttendanceDuration => self::fromDomain($x, $attendanceId),
            $durations
        );
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Shift\Duration $domain
     * @param int $attendanceId
     * @return static
     */
    public static function fromDomain(DomainDuration $domain, int $attendanceId): self
    {
        $values = self::getDomainValues($domain, ['activity', 'duration']);
        $attributes = [
            'attendance_id' => $attendanceId,
        ];
        return self::firstOrNew($attributes)->fill($values);
    }
}

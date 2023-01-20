<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Common\Carbon;
use Domain\Entity;

/**
 * 勤務実績.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read int $contractId 契約ID
 * @property-read int $officeId 事業所UD
 * @property-read null|int $userId 利用者ID
 * @property-read int $assignerId 管理スタッフID
 * @property-read \Domain\Shift\Task $task 勤務区分
 * @property-read null|\Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read int $headcount 頭数
 * @property-read \Domain\Shift\Assignee[] $assignees 担当スタッフ
 * @property-read \Domain\Common\Schedule $schedule スケジュール
 * @property-read \Domain\Shift\Duration[] $durations 勤務時間
 * @property-read \Domain\Shift\ServiceOption[] $options オプション
 * @property-read string $note 備考
 * @property-read bool $isConfirmed 確定フラグ
 * @property-read bool $isCanceled キャンセルフラグ
 * @property-read string $reason キャンセル理由
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class Attendance extends Entity
{
    use HasServiceOptions;

    /**
     * 勤務シフトからインスタンスを生成する.
     *
     * @param \Domain\Shift\Shift $shift
     * @param null|\Domain\Common\Carbon $now 生成時刻. 固定したい場合にセットする.
     * @return static
     */
    public static function createFromShift(Shift $shift, Carbon $now = null): self
    {
        $setNow = $now ?? Carbon::now();
        return self::create([
            'organizationId' => $shift->organizationId,
            'contractId' => $shift->contractId,
            'officeId' => $shift->officeId,
            'userId' => $shift->userId,
            'assignerId' => $shift->assignerId,
            'task' => $shift->task,
            'serviceCode' => $shift->serviceCode,
            'headcount' => $shift->headcount,
            'assignees' => $shift->assignees,
            'schedule' => $shift->schedule,
            'durations' => $shift->durations,
            'options' => $shift->options,
            'note' => $shift->note,
            'isConfirmed' => false,
            'isCanceled' => false,
            'reason' => $shift->reason,
            'updatedAt' => $setNow,
            'createdAt' => $setNow,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        $attrs = [
            'organizationId',
            'contractId',
            'officeId',
            'userId',
            'assignerId',
            'task',
            'serviceCode',
            'headcount',
            'assignees',
            'schedule',
            'durations',
            'options',
            'note',
            'isConfirmed',
            'isCanceled',
            'reason',
            'updatedAt',
            'createdAt',
        ];
        return array_merge(parent::attrs(), $attrs);
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'organizationId' => false,
            'contractId' => true,
            'officeId' => true,
            'userId' => true,
            'assignerId' => true,
            'task' => true,
            'serviceCode' => true,
            'headcount' => true,
            'assignees' => true,
            'schedule' => true,
            'durations' => true,
            'options' => true,
            'note' => true,
            'isConfirmed' => true,
            'isCanceled' => true,
            'reason' => true,
            'updatedAt' => true,
            'createdAt' => true,
        ];
    }
}

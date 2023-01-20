<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Entity;

/**
 * 勤務シフト.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read null|int $contractId 契約ID
 * @property-read int $officeId 事業所ID
 * @property-read null|int $userId 利用者ID
 * @property-read int $assignerId 管理スタッフID
 * @property-read \Domain\Shift\Task $task 勤務区分
 * @property-read null|\Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read string $headcount 頭数
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
final class Shift extends Entity
{
    use HasServiceOptions;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
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
            'createdAt',
            'updatedAt',
        ];
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
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

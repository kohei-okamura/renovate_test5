<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Model;

/**
 * 障害福祉サービス：計画：週間サービス計画.
 *
 * @property-read int $summaryIndex 週間サービス計画番号
 * @property-read \Domain\Project\DwsProjectServiceCategory $category サービス区分
 * @property-read \Domain\Common\Recurrence $recurrence 繰り返し周期
 * @property-read \Domain\Common\DayOfWeek[] $dayOfWeeks 曜日
 * @property-read \Domain\Common\TimeRange $slot 時間帯
 * @property-read int $headcount 提供人数
 * @property-read null|int $ownExpenseProgramId 自費サービス情報 ID
 * @property-read \Domain\Shift\ServiceOption[] $options サービスオプション
 * @property-read \Domain\Project\DwsProjectContent[] $contents サービス詳細
 * @property-read string $note 備考
 */
final class DwsProjectProgram extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'summaryIndex',
            'category',
            'recurrence',
            'dayOfWeeks',
            'slot',
            'headcount',
            'ownExpenseProgramId',
            'options',
            'contents',
            'note',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'summaryIndex' => true,
            'category' => true,
            'recurrence' => true,
            'dayOfWeeks' => true,
            'slot' => true,
            'headcount' => true,
            'ownExpenseProgramId' => true,
            'options' => true,
            'contents' => true,
            'note' => true,
        ];
    }
}

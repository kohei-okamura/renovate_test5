<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Model;

/**
 * 介護保険サービス：計画：週間サービス計画.
 *
 * @property-read int $programIndex 週間サービス計画番号
 * @property-read \Domain\Project\LtcsProjectServiceCategory $category サービス区分
 * @property-read \Domain\Common\Recurrence $recurrence 繰り返し周期
 * @property-read \Domain\Common\DayOfWeek[] $dayOfWeeks 曜日
 * @property-read \Domain\Common\TimeRange $slot 時間帯
 * @property-read \Domain\ServiceCodeDictionary\Timeframe $timeframe 算定時間帯
 * @property-read \Domain\Project\LtcsProjectAmount[] $amounts サービス提供量
 * @property-read int $headcount 提供人数
 * @property-read null|int $ownExpenseProgramId 自費サービス情報 ID
 * @property-read \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read \Domain\Shift\ServiceOption[] $options サービスオプション
 * @property-read \Domain\Project\LtcsProjectContent[] $contents サービス詳細
 * @property-read string $note 備考
 */
final class LtcsProjectProgram extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'programIndex',
            'category',
            'recurrence',
            'dayOfWeeks',
            'slot',
            'timeframe',
            'amounts',
            'headcount',
            'ownExpenseProgramId',
            'serviceCode',
            'options',
            'contents',
            'note',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'programIndex' => true,
            'category' => true,
            'recurrence' => true,
            'dayOfWeeks' => true,
            'slot' => true,
            'timeframe' => true,
            'amounts' => true,
            'headcount' => true,
            'ownExpenseProgramId' => true,
            'serviceCode' => true,
            'options' => true,
            'contents' => true,
            'note' => true,
        ];
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Enum;

/**
 * 勤務内容.
 *
 * @method static Activity dwsPhysicalCare() 居宅：身体
 * @method static Activity dwsHousework() 居宅：家事
 * @method static Activity dwsAccompanyWithPhysicalCare() 居宅：通院・身体あり
 * @method static Activity dwsAccompany() 居宅：通院・身体なし
 * @method static Activity dwsVisitingCareForPwsd() 重訪
 * @method static Activity dwsOutingSupportForPwsd() 重訪（移動加算）
 * @method static Activity ltcsPhysicalCare() 介保：身体
 * @method static Activity ltcsHousework() 介保：生活
 * @method static Activity commAccompanyWithPhysicalCare() 移動支援・身体あり
 * @method static Activity commAccompany() 移動支援・身体なし
 * @method static Activity comprehensive() 総合事業
 * @method static Activity ownExpense() 自費
 * @method static Activity fieldwork() 実地研修
 * @method static Activity assessment() アセスメント
 * @method static Activity visit() その他往訪
 * @method static Activity officeWork() 事務
 * @method static Activity sales() 営業
 * @method static Activity meeting() ミーティング
 * @method static Activity other() その他
 * @method static Activity resting() 休憩
 */
final class Activity extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'dwsPhysicalCare' => 101101,
        'dwsHousework' => 101102,
        'dwsAccompanyWithPhysicalCare' => 101103,
        'dwsAccompany' => 101104,
        'dwsVisitingCareForPwsd' => 101201,
        'dwsOutingSupportForPwsd' => 101202,
        'ltcsPhysicalCare' => 201101,
        'ltcsHousework' => 201102,
        'commAccompanyWithPhysicalCare' => 111101,
        'commAccompany' => 111102,
        'comprehensive' => 211101,
        'ownExpense' => 711101,
        'fieldwork' => 811101,
        'assessment' => 811102,
        'visit' => 899999,
        'officeWork' => 911101,
        'sales' => 911102,
        'meeting' => 911103,
        'other' => 988888,
        'resting' => 999999,
    ];
}

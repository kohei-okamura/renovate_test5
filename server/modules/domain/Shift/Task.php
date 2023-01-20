<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Enum;

/**
 * 勤務区分.
 *
 * @method static Task dwsPhysicalCare() 居宅：身体
 * @method static Task dwsHousework() 居宅：家事
 * @method static Task dwsAccompanyWithPhysicalCare() 居宅：通院・身体
 * @method static Task dwsAccompany() 居宅：通院
 * @method static Task dwsVisitingCareForPwsd() 重度訪問介護
 * @method static Task ltcsPhysicalCare() 介保：身体
 * @method static Task ltcsHousework() 介保：生活
 * @method static Task ltcsPhysicalCareAndHousework() 介保：身体・生活
 * @method static Task commAccompanyWithPhysicalCare() 移動支援・身体
 * @method static Task commAccompany() 移動支援
 * @method static Task comprehensive() 総合事業
 * @method static Task ownExpense() 自費
 * @method static Task fieldwork() 実地研修
 * @method static Task assessment() アセスメント
 * @method static Task visit() その他往訪
 * @method static Task officeWork() 事務
 * @method static Task sales() 営業
 * @method static Task meeting() ミーティング
 * @method static Task other() その他
 */
final class Task extends Enum
{
    use TaskSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'dwsPhysicalCare' => 101101,
        'dwsHousework' => 101102,
        'dwsAccompanyWithPhysicalCare' => 101103,
        'dwsAccompany' => 101104,
        'dwsVisitingCareForPwsd' => 101201,
        'ltcsPhysicalCare' => 201101,
        'ltcsHousework' => 201102,
        'ltcsPhysicalCareAndHousework' => 201103,
        'commAccompanyWithPhysicalCare' => 111101,
        'commAccompany' => 111102,
        'comprehensive' => 211101,
        'ownExpense' => 701101,
        'fieldwork' => 801101,
        'assessment' => 801102,
        'visit' => 899999,
        'officeWork' => 901101,
        'sales' => 901102,
        'meeting' => 901103,
        'other' => 988888,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        101101 => '居宅：身体',
        101102 => '居宅：家事',
        101103 => '居宅：通院・身体',
        101104 => '居宅：通院',
        101201 => '重度訪問介護',
        201101 => '介保：身体',
        201102 => '介保：生活',
        201103 => '介保：身体・生活',
        111101 => '移動支援・身体',
        111102 => '移動支援',
        211101 => '総合事業',
        701101 => '自費',
        801101 => '実地研修',
        801102 => 'アセスメント',
        899999 => 'その他往訪',
        901101 => '事務',
        901102 => '営業',
        901103 => 'ミーティング',
        988888 => 'その他',
    ];

    /**
     * Resolve Task to label.
     *
     * @param \Domain\Shift\Task $x
     * @return string
     */
    public static function resolve(Task $x): string
    {
        return self::$map[$x->value()];
    }
}

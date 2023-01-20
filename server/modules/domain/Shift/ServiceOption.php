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
 * サービスオプション.
 *
 * @method static ServiceOption notificationEnabled() 通知
 * @method static ServiceOption oneOff() 単発
 * @method static ServiceOption firstTime() 初回
 * @method static ServiceOption emergency() 緊急時対応
 * @method static ServiceOption sucking() 喀痰吸引
 * @method static ServiceOption welfareSpecialistCooperation() 福祉専門職員等連携
 * @method static ServiceOption plannedByNovice() 初計
 * @method static ServiceOption providedByBeginner() 基礎研修課程修了者等
 * @method static ServiceOption providedByCareWorkerForPwsd() 重研
 * @method static ServiceOption over20() 同一建物減算
 * @method static ServiceOption over50() 同一建物減算（大規模）
 * @method static ServiceOption behavioralDisorderSupportCooperation() 行動障害支援連携
 * @method static ServiceOption hospitalized() 入院
 * @method static ServiceOption longHospitalized() 入院（長期）
 * @method static ServiceOption coaching() 熟練同行
 * @method static ServiceOption vitalFunctionsImprovement1() 生活機能向上連携Ⅰ
 * @method static ServiceOption vitalFunctionsImprovement2() 生活機能向上連携Ⅱ
 */
final class ServiceOption extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'notificationEnabled' => 100001,
        'oneOff' => 100002,
        'firstTime' => 300001,
        'emergency' => 300002,
        'sucking' => 300003,
        'welfareSpecialistCooperation' => 301101,
        'plannedByNovice' => 301102,
        'providedByBeginner' => 301103,
        'providedByCareWorkerForPwsd' => 301104,
        'over20' => 301105,
        'over50' => 301106,
        'behavioralDisorderSupportCooperation' => 301201,
        'hospitalized' => 301202,
        'longHospitalized' => 301203,
        'coaching' => 301204,
        'vitalFunctionsImprovement1' => 401101,
        'vitalFunctionsImprovement2' => 401102,
    ];
}

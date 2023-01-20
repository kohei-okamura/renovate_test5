<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Enum;

/**
 * 障害福祉サービス：決定サービスコード.
 *
 * @method static DwsGrantedServiceCode none() なし
 * @method static DwsGrantedServiceCode physicalCare() 居宅介護身体介護
 * @method static DwsGrantedServiceCode housework() 居宅介護家事援助
 * @method static DwsGrantedServiceCode accompanyWithPhysicalCare() 居宅介護通院介助（身体介護を伴う）
 * @method static DwsGrantedServiceCode accompany() 居宅介護通院介助（身体介護を伴わない）
 * @method static DwsGrantedServiceCode visitingCareForPwsd1() 重度訪問介護（重度障害者等包括支援対象者）
 * @method static DwsGrantedServiceCode visitingCareForPwsd2() 重度訪問介護（障害支援区分6該当者）
 * @method static DwsGrantedServiceCode visitingCareForPwsd3() 重度訪問介護（その他）
 * @method static DwsGrantedServiceCode outingSupportForPwsd() 重度訪問介護（移動加算）
 * @method static DwsGrantedServiceCode comprehensiveSupport() 重度包括基本
 */
final class DwsGrantedServiceCode extends Enum
{
    use DwsGrantedServiceCodeSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => '000000',
        'physicalCare' => '111000',
        'housework' => '112000',
        'accompanyWithPhysicalCare' => '113000',
        'accompany' => '114000',
        'visitingCareForPwsd1' => '121000',
        'visitingCareForPwsd2' => '122000',
        'visitingCareForPwsd3' => '123000',
        'outingSupportForPwsd' => '120901',
        'comprehensiveSupport' => '141000',
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        '000000' => 'なし',
        '111000' => '居宅介護身体介護',
        '112000' => '居宅介護家事援助',
        '113000' => '居宅介護通院介助（身体介護を伴う）',
        '114000' => '居宅介護通院介助（身体介護を伴わない）',
        '121000' => '重度訪問介護（重度障害者等包括支援対象者）',
        '122000' => '重度訪問介護（障害支援区分6該当者）',
        '123000' => '重度訪問介護（その他）',
        '120901' => '重度訪問介護（移動加算）',
        '141000' => '重度包括基本',
    ];

    /**
     * Resolve DwsGrantedServiceCode to label.
     *
     * @param \Domain\Billing\DwsGrantedServiceCode $x
     * @return string
     */
    public static function resolve(DwsGrantedServiceCode $x): string
    {
        return self::$map[$x->value()];
    }
}

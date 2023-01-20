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
 * 介護保険サービス：明細書：中止理由.
 *
 * @method static LtcsExpiredReason unspecified() 未設定
 * @method static LtcsExpiredReason notApplicable() 非該当
 * @method static LtcsExpiredReason hospitalized() 医療機関入院
 * @method static LtcsExpiredReason died() 死亡
 * @method static LtcsExpiredReason other() その他
 * @method static LtcsExpiredReason admittedToWelfareFacility() 介護老人福祉施設入所
 * @method static LtcsExpiredReason admittedToHealthCareFacility() 介護老人保健施設入所
 * @method static LtcsExpiredReason admittedToMedicalLongTermCareSanatoriums() 介護療養型医療施設入所
 * @method static LtcsExpiredReason admittedToCareAidMedicalCenter() 介護医療院入所
 */
final class LtcsExpiredReason extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'unspecified' => 0,
        'notApplicable' => 1,
        'hospitalized' => 3,
        'died' => 4,
        'other' => 5,
        'admittedToWelfareFacility' => 6,
        'admittedToHealthCareFacility' => 7,
        'admittedToMedicalLongTermCareSanatoriums' => 8,
        'admittedToCareAidMedicalCenter' => 9,
    ];
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\LtcsInsCard;

use Domain\Enum;

/**
 * 介護保険被保険者証：サービスの種類.
 *
 * @method static LtcsInsCardServiceType serviceType1() サービス種別1
 * @method static LtcsInsCardServiceType serviceType2() サービス種別2
 * @method static LtcsInsCardServiceType serviceType3() サービス種別3
 */
final class LtcsInsCardServiceType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'serviceType1' => 1,
        'serviceType2' => 2,
        'serviceType3' => 3,
    ];
}

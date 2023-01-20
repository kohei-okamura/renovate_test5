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
 * 介護保険被保険者証：認定区分.
 *
 * @method static LtcsInsCardStatus applied() 申請中
 * @method static LtcsInsCardStatus approved() 認定済
 */
final class LtcsInsCardStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'applied' => 1,
        'approved' => 2,
    ];
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Enum;

/**
 * 障害種別.
 *
 * @method static DwsType physical() 身体
 * @method static DwsType intellectual() 知的
 * @method static DwsType mental() 精神
 * @method static DwsType intractableDiseases() 難病
 */
final class DwsType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'physical' => 1,
        'intellectual' => 2,
        'mental' => 3,
        'intractableDiseases' => 5,
    ];
}

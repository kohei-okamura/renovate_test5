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
 * 上限管理区分.
 *
 * @method static CopayCoordinationType none() 上限管理なし
 * @method static CopayCoordinationType internal() 自社事業所
 * @method static CopayCoordinationType external() 他社事業所
 * @method static CopayCoordinationType unknown() 不明
 */
final class CopayCoordinationType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 1,
        'internal' => 2,
        'external' => 3,
        'unknown' => 9,
    ];
}

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
 * 障害福祉サービス受給者証：認定区分.
 *
 * @method static DwsCertificationStatus applied() 申請中
 * @method static DwsCertificationStatus approved() 認定済
 */
final class DwsCertificationStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'applied' => 1,
        'approved' => 2,
    ];
}

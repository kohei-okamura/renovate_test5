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
 * 介護保険サービス：居宅サービス計画作成区分.
 *
 * @method static LtcsCarePlanAuthorType careManagerOffice() 居宅介護支援事業所作成
 * @method static LtcsCarePlanAuthorType self() 自己作成
 * @method static LtcsCarePlanAuthorType preventionOffice() 介護予防支援事業所・地域包括支援センター作成
 */
final class LtcsCarePlanAuthorType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'careManagerOffice' => 1,
        'self' => 2,
        'preventionOffice' => 3,
    ];
}

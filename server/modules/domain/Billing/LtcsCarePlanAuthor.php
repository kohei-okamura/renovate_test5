<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\Polite;

/**
 * 介護保険サービス：請求：居宅サービス計画.
 */
final class LtcsCarePlanAuthor extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsCarePlanAuthor} constructor.
     *
     * @param \Domain\LtcsInsCard\LtcsCarePlanAuthorType $authorType 居宅サービス計画作成区分
     * @param null|int $officeId 事業所 ID
     * @param string $code 事業所番号
     * @param string $name 事業所名
     */
    public function __construct(
        public readonly LtcsCarePlanAuthorType $authorType,
        public readonly ?int $officeId,
        public readonly string $code,
        public readonly string $name
    ) {
    }
}

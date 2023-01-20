<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Addr;
use Domain\Office\Office;
use Domain\Polite;
use Lib\Exceptions\NotFoundException;

/**
 * 介護保険サービス：請求：事業所.
 */
final class LtcsBillingOffice extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingOffice} constructor.
     *
     * @param int $officeId 事業所 ID
     * @param string $code 事業所番号
     * @param string $name 事業所名
     * @param string $abbr 事業所名（略称）
     * @param \Domain\Common\Addr $addr 所在地
     * @param string $tel 電話番号
     */
    public function __construct(
        public readonly int $officeId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $abbr,
        public readonly Addr $addr,
        public readonly string $tel
    ) {
    }

    /**
     * 事業所モデルからインスタンスを生成する.
     *
     * @param \Domain\Office\Office $office
     * @return static
     */
    public static function from(Office $office): self
    {
        if ($office->ltcsHomeVisitLongTermCareService === null) {
            throw new NotFoundException('No LtcsHomeVisitLongTermCareService');
        }
        return new self(
            officeId: $office->id,
            code: $office->ltcsHomeVisitLongTermCareService->code,
            name: $office->name,
            abbr: $office->abbr,
            addr: $office->addr,
            tel: $office->tel,
        );
    }
}

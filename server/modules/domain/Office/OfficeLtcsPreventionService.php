<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Common\Carbon;
use Domain\Polite;

/**
 * 事業所：介護保険サービス：介護予防支援.
 */
final class OfficeLtcsPreventionService extends Polite
{
    /**
     * {@link \Domain\Office\OfficeLtcsPreventionService} constructor.
     *
     * @param string $code 事業所番号
     * @param null|\Domain\Common\Carbon $openedOn 開設日
     * @param null|\Domain\Common\Carbon $designationExpiredOn 指定更新期日
     */
    public function __construct(
        public readonly string $code,
        public readonly ?Carbon $openedOn,
        public readonly ?Carbon $designationExpiredOn,
    ) {
    }
}

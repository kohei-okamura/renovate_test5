<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 利用者負担額一覧表元データ.
 */
final class CopayListSource extends Polite
{
    /**
     * {@link \Domain\Billing\CopayListSource} constructor.
     *
     * @param string $copayCoordinationOfficeName 上限管理事業所名
     * @param array&\Domain\Billing\DwsBillingStatement[] $statements 障害福祉サービス：明細書
     */
    public function __construct(
        public readonly string $copayCoordinationOfficeName,
        public readonly array $statements,
    ) {
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス詳細一覧組み立てユースケース.
 */
interface BuildDwsBillingServiceDetailListUseCase
{
    /**
     * 障害福祉サービス：サービス詳細の一覧（市町村番号別）を組み立てる.
     *
     * `Seq` の要素は下記のキーを持つ連想配列.
     *
     * ## `cityCode`
     * 市町村番号（文字列）
     *
     * ## `cityName`
     * 市町村名（文字列）
     *
     * ## `details`
     * {@link \Domain\Billing\DwsBillingServiceDetail} の配列
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBillingSource[]|\ScalikePHP\Seq $sources
     * @throws \Throwable
     * @return array[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Office $office, Carbon $providedIn, Seq $sources): Seq;
}

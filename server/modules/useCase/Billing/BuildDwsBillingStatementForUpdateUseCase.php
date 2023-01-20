<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;

/**
 * 障害福祉サービス：請求：明細書 更新用生成ユースケース.
 */
interface BuildDwsBillingStatementForUpdateUseCase
{
    /**
     * 障害福祉サービス：請求：明細書 更新用生成処理.
     *
     * @param \Domain\Context\Context $context コンテクスト
     * @param \Domain\Billing\DwsBillingStatement $entityForUpdate 更新したいエンティティ
     * @return \Domain\Billing\DwsBillingStatement 更新を受けて再計算した保存用のエンティティ
     */
    public function handle(Context $context, DwsBillingStatement $entityForUpdate): DwsBillingStatement;
}

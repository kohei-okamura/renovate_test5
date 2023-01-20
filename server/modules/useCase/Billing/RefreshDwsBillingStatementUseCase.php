<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 障害福祉サービス：明細書等リフレッシュユースケース.
 */
interface RefreshDwsBillingStatementUseCase
{
    /**
     * 障害福祉サービス：明細書等をリフレッシュする.
     *
     * 請求単位が増える場合
     *  - 請求単位を登録
     *  - 請求書を登録
     *
     * 請求単位が消える場合
     *  - 請求単位を削除
     *  - 請求書を削除
     *
     * 請求単位に利用者が増える場合
     *  - 請求単位を更新（利用者に対応するサービス詳細を追加）
     *  - 請求書を更新
     *
     * 請求単位から利用者が減る場合
     *  - 請求単位を更新（利用者に対応するサービス詳細を削除）
     *  - 請求書を更新
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param array $ids
     * @return void
     */
    public function handle(Context $context, int $billingId, array $ids): void;
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書簡易取得ユースケース.
 */
interface SimpleLookupLtcsBillingStatementUseCase
{
    /**
     * ID を指定して介護保険サービス：明細書を取得する.
     *
     * 基本的には LookupLtcsBillingStatementUseCase を使用すること.
     * 明細書IDしかない場合のみこのユースケースを利用すること.
     *
     * 国保連請求実装時には厳密に保証して取得していたが利用者請求実装時に
     * ID で取得したいケースが発生したため簡易取得ユースケースを追加した
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$ids
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Permission $permission,
        int ...$ids
    ): Seq;
}

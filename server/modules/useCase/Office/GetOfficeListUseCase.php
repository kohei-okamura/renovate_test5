<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 事業所情報取得（権限指定なし）ユースケース.
 */
interface GetOfficeListUseCase
{
    /**
     * IDを指定して事業所情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$ids 指定しない場合は全件
     * @return \Domain\Office\Office[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$ids): Seq;
}

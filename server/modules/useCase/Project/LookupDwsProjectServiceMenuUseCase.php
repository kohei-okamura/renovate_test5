<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：計画：サービス内容取得ユースケース.
 */
interface LookupDwsProjectServiceMenuUseCase
{
    /**
     * ID を指定して障害福祉サービス：計画：サービス内容を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$ids
     * @return \Domain\Project\DwsProjectServiceMenu[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$ids): Seq;
}

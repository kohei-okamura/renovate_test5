<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス名称導出ユースケース.
 */
interface ResolveLtcsNameFromServiceCodesUseCase
{
    /**
     * サービスコードからサービス名称を導出する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ServiceCode\ServiceCode[]&\ScalikePHP\Seq $serviceCodes
     * @param \Domain\Common\Carbon $providedIn
     * @return \ScalikePHP\Map key=サービスコード value=サービス名称
     */
    public function handle(Context $context, Seq $serviceCodes, Carbon $providedIn): Map;
}

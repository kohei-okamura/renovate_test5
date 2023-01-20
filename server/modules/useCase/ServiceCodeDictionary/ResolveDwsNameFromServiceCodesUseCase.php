<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Context\Context;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス名称導出ユースケース.
 */
interface ResolveDwsNameFromServiceCodesUseCase
{
    /**
     * サービスコードからサービス名称を導出する.
     *
     * 辞書が更新されても同一のサービスコードならばサービス名称は変わらないという前提で
     * Dictionary で辞書は特定せずに、Entryだけをfindし、サービスコードが存在している最新のサービス辞書のデータを
     * 取得する という思想で実装している.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ServiceCode\ServiceCode[]|\ScalikePHP\Seq $serviceCodes
     * @return \ScalikePHP\Map key=サービスコード value=サービス名称
     */
    public function handle(Context $context, Seq $serviceCodes): Map;
}

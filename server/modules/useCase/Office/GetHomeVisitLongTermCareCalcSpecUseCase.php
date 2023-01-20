<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 事業所 ID とサービス提供年月で介護保険サービス：訪問介護：算定情報取得ユースケース.
 */
interface GetHomeVisitLongTermCareCalcSpecUseCase
{
    /**
     * 事業所 ID とサービス提供年月を指定して介護保険サービス：訪問介護：算定情報を取得する.
     *
     * 介護保険サービス：訪問介護：算定情報特定ユースケースがあるが、そっちでは事業所を引数で受け取る必要があるため
     * 介護保険サービス：訪問介護：算定情報特定 API で呼び出す用にこのユースケースを追加した.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Office\HomeVisitLongTermCareCalcSpec[]|\ScalikePHP\Option
     */
    public function handle(Context $context, array $permissions, int $officeId, Carbon $providedIn): Option;
}

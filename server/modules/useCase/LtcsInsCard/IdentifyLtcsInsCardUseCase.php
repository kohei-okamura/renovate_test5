<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\User\User;
use ScalikePHP\Option;

/**
 * 介護保険被保険者証特定ユースケース.
 */
interface IdentifyLtcsInsCardUseCase
{
    /**
     * 対象年月日において有効な介護保険被保険者証を返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary[]|\ScalikePHP\Option
     */
    public function handle(Context $context, User $user, Carbon $targetDate): Option;
}

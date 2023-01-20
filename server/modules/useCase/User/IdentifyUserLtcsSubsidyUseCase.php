<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\User\User;
use ScalikePHP\Seq;

/**
 * 利用者：公費情報特定ユースケース.
 */
interface IdentifyUserLtcsSubsidyUseCase
{
    /**
     * 指定した時点で有効な利用者：公費情報を特定してその一覧を返す.
     *
     * - 適用優先順位順（＝列挙型の定義順）に並び替える.
     * - 最大3件に制限する.
     * - 3件に満たない場合も常に3件返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\User\UserLtcsSubsidy[][]&\ScalikePHP\Option[]&\ScalikePHP\Seq
     */
    public function handle(Context $context, User $user, Carbon $targetDate): Seq;
}

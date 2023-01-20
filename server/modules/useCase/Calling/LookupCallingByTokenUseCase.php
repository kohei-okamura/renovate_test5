<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * トークンを用いた出勤確認情報取得ユースケース.
 */
interface LookupCallingByTokenUseCase
{
    /**
     * トークンを指定して出勤確認情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @return \Domain\Calling\Calling|\ScalikePHP\Option
     */
    public function handle(Context $context, string $token): Option;
}

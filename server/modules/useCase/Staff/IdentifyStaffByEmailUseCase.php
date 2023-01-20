<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * メールアドレスを用いたスタッフ情報特定ユースケース.
 */
interface IdentifyStaffByEmailUseCase
{
    /**
     * メールアドレスを指定して有効かつ退職ではないスタッフ情報を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param string $email
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    public function handle(Context $context, string $email): Option;
}

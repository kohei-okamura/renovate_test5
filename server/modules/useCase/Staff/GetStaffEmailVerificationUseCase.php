<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\StaffEmailVerification;

/**
 * スタッフメールアドレス検証エンティティ取得ユースケース.
 */
interface GetStaffEmailVerificationUseCase
{
    /**
     * スタッフのメールアドレスを検証エンティティを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @throws \Lib\Exceptions\ForbiddenException
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\Staff\StaffEmailVerification
     */
    public function handle(Context $context, string $token): StaffEmailVerification;
}

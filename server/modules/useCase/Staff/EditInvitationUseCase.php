<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Invitation;

/**
 * 招待編集ユースケース.
 */
interface EditInvitationUseCase
{
    /**
     * 招待を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values ['staffId' => $staff->id]
     * @return \Domain\Staff\Invitation
     */
    public function handle(Context $context, int $id, array $values): Invitation;
}

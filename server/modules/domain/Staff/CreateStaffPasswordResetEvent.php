<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Context\Context;
use Domain\Event\Event;

/**
 * スタッフパスワード再設定作成イベント.
 *
 * @see \App\Listeners\CreateStaffPasswordResetEventListener
 */
final class CreateStaffPasswordResetEvent extends Event
{
    private StaffPasswordReset $passwordReset;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\StaffPasswordReset $passwordReset
     */
    public function __construct(Context $context, StaffPasswordReset $passwordReset)
    {
        parent::__construct($context);
        $this->passwordReset = $passwordReset;
    }

    /**
     * @return \Domain\Staff\StaffPasswordReset
     */
    public function passwordReset()
    {
        return $this->passwordReset;
    }
}

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
 * スタッフログインイベント.
 */
final class StaffLoggedInEvent extends Event
{
    private bool $rememberMe;
    private Staff $staff;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     * @param bool $rememberMe
     */
    public function __construct(Context $context, Staff $staff, bool $rememberMe)
    {
        parent::__construct($context);
        $this->rememberMe = $rememberMe;
        $this->staff = $staff;
    }

    /**
     * @return bool
     */
    public function rememberMe(): bool
    {
        return $this->rememberMe;
    }

    /**
     * @return \Domain\Staff\Staff
     */
    public function staff(): Staff
    {
        return $this->staff;
    }
}

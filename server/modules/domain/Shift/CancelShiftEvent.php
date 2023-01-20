<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Context\Context;
use Domain\Event\Event;
use Domain\Staff\Staff;
use Domain\User\User;

/**
 * 勤務シフトキャンセルイベント.
 */
class CancelShiftEvent extends Event
{
    private Shift $shift;
    private Staff $staff;
    private ?User $user;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Shift\Shift $shift
     * @param \Domain\Staff\Staff $staff
     * @param \Domain\User\User $user
     */
    public function __construct(Context $context, Shift $shift, Staff $staff, ?User $user = null)
    {
        parent::__construct($context);
        $this->shift = $shift;
        $this->staff = $staff;
        $this->user = $user;
    }

    /**
     * 勤務シフトの取得.
     *
     * @return \Domain\Shift\Shift
     */
    public function shift(): Shift
    {
        return $this->shift;
    }

    /**
     * スタッフの取得.
     *
     * @return \Domain\Staff\Staff
     */
    public function staff(): Staff
    {
        return $this->staff;
    }

    /**
     * 利用者の取得.
     *
     * @return \Domain\User\User
     */
    public function user(): User
    {
        return $this->user;
    }
}

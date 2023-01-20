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
 * 計画更新イベント.
 *
 * @see \App\Listeners\UpdateShiftEventListener
 */
final class UpdateShiftEvent extends Event
{
    private Shift $originalShift;
    private Shift $updatedShift;
    private User $originalUser;
    private User $updatedUser;
    private Staff $staff;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Shift\Shift $originalShift
     * @param \Domain\Shift\Shift $updatedShift
     * @param \Domain\User\User $originalUser
     * @param \Domain\User\User $updatedUser
     * @param \Domain\Staff\Staff $staff
     */
    public function __construct(
        Context $context,
        Shift $originalShift,
        Shift $updatedShift,
        User $originalUser,
        User $updatedUser,
        Staff $staff
    ) {
        parent::__construct($context);
        $this->originalShift = $originalShift;
        $this->updatedShift = $updatedShift;
        $this->originalUser = $originalUser;
        $this->updatedUser = $updatedUser;
        $this->staff = $staff;
    }

    /**
     * 変更前の勤務シフトを取得.
     *
     * @return \Domain\Shift\Shift
     */
    public function originalShift(): Shift
    {
        return $this->originalShift;
    }

    /**
     * 変更後の勤務シフトを取得.
     *
     * @return \Domain\Shift\Shift
     */
    public function updatedShift(): Shift
    {
        return $this->updatedShift;
    }

    /**
     * 変更前の利用者を取得.
     *
     * @return \Domain\User\User
     */
    public function originalUser(): User
    {
        return $this->originalUser;
    }

    /**
     * 変更後の利用者を取得.
     *
     * @return \Domain\User\User
     */
    public function updatedUser(): User
    {
        return $this->updatedUser;
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
}

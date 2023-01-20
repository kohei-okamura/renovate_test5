<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Domain\Shift\Shift;
use Domain\Staff\Staff;
use Domain\User\User;
use ScalikePHP\Option;

/**
 * 勤務シフトキャンセルメール.
 */
class CancelShiftMailBuilder extends AbstractMailBuilder
{
    private Shift $shift;
    private Staff $staff;
    private ?User $user;

    /**
     * Shiftをセットする.
     *
     * @param \Domain\Shift\Shift $shift
     * @return $this
     */
    public function shift(Shift $shift): self
    {
        $this->shift = $shift;
        return $this;
    }

    /**
     * Staffをセットする.
     *
     * @param \Domain\Staff\Staff $staff
     * @return $this
     */
    public function staff(Staff $staff): self
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * Userをセットする.
     *
     * @param \Domain\User\User $user
     * @return $this
     */
    public function user(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /** {@inheritdoc} */
    protected function subject(): string
    {
        return '勤務シフトがキャンセルされました';
    }

    /** {@inheritdoc} */
    protected function view(): string
    {
        return 'emails.shift.cancel';
    }

    /** {@inheritdoc} */
    protected function params(): array
    {
        $schedule = $this->shift->schedule->date->isoFormat('M月D日（ddd） ')
            . $this->shift->schedule->start->format('H:i〜')
            . $this->shift->schedule->end->format('H:i');
        return [
            'schedule' => $schedule,
            'staff' => $this->staff,
            'userName' => Option::from($this->user)
                ->map(fn (User $x): string => $x->name->displayName)
                ->getOrElseValue(''),
            'note' => $this->shift->note,
        ];
    }
}

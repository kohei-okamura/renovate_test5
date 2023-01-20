<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Domain\Shift\Shift;
use Domain\Staff\Staff;
use Domain\User\User;
use Laravel\Lumen\Routing\UrlGenerator;

/**
 * 勤務シフト更新メール.
 */
class UpdateShiftMailBuilder extends AbstractMailBuilder
{
    private Shift $originalShift;
    private Shift $updatedShift;
    private User $originalUser;
    private User $updatedUser;
    private Staff $staff;
    private UrlGenerator $url;

    /**
     * Constructor.
     *
     * @param \Laravel\Lumen\Routing\UrlGenerator $url
     */
    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    /**
     * 変更前の Shift をセットする.
     *
     * @param \Domain\Shift\Shift $originalShift
     * @return $this
     */
    public function originalShift(Shift $originalShift): self
    {
        $this->originalShift = $originalShift;
        return $this;
    }

    /**
     * 変更後の Shift をセットする.
     *
     * @param \Domain\Shift\Shift $shift
     * @return $this
     */
    public function updatedShift(Shift $shift): self
    {
        $this->updatedShift = $shift;
        return $this;
    }

    /**
     * 変更前の User をセットする.
     *
     * @param \Domain\User\User $originalUser
     * @return $this
     */
    public function originalUser(User $originalUser): self
    {
        $this->originalUser = $originalUser;
        return $this;
    }

    /**
     * 変更後の User をセットする.
     *
     * @param \Domain\User\User $shift
     * @return $this
     */
    public function updatedUser(User $shift): self
    {
        $this->updatedUser = $shift;
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
     * 件名.
     *
     * @return string
     */
    protected function subject(): string
    {
        return '勤務シフトが変更されました';
    }

    /**
     * メールテンプレート名.
     *
     * @return string
     */
    protected function view(): string
    {
        return 'emails.shift.update';
    }

    /**
     * View に渡すパラメータ.
     *
     * @return array
     */
    protected function params(): array
    {
        $originalSchedule = $this->originalShift->schedule->date->isoFormat('M月D日（ddd） ')
            . $this->originalShift->schedule->start->format('H:i〜')
            . $this->originalShift->schedule->end->format('H:i');
        $updatedSchedule = $this->updatedShift->schedule->date->isoFormat('M月D日（ddd） ')
            . $this->updatedShift->schedule->start->format('H:i〜')
            . $this->updatedShift->schedule->end->format('H:i');
        return [
            'staff' => $this->staff,
            'originalSchedule' => $originalSchedule,
            'updatedSchedule' => $updatedSchedule,
            'originalUserName' => $this->originalUser->name->displayName,
            'updatedUserName' => $this->updatedUser->name->displayName,
            'note' => $this->updatedShift->note,
        ];
    }
}

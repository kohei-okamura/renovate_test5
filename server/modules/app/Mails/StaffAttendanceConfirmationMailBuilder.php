<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Domain\Calling\Calling;
use Domain\Staff\Staff;
use Lib\Logging;

/**
 * スタッフ出勤確認メール.
 */
class StaffAttendanceConfirmationMailBuilder extends AbstractMailBuilder
{
    use Logging;

    private Calling $calling;
    private Staff $staff;
    private string $url;

    /**
     * Calling をセットする.
     *
     * @param \Domain\Calling\Calling $calling
     * @return $this
     */
    public function calling(Calling $calling): self
    {
        $this->calling = $calling;
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
     * URLをセットする.
     *
     * @param string $url
     * @return $this
     */
    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /** {@inheritdoc} */
    protected function subject(): string
    {
        return '本日のシフトが2時間後に開始されます';
    }

    /** {@inheritdoc} */
    protected function view(): string
    {
        return 'emails.shift.confirmation';
    }

    /** {@inheritdoc} */
    protected function params(): array
    {
        return [
            'expiredAt' => $this->calling->expiredAt->format('H:i'),
            'staff' => $this->staff,
            'url' => $this->url,
        ];
    }
}

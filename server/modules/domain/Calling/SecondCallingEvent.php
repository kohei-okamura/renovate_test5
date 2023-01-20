<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Context\Context;
use Domain\Event\Event;
use Domain\Shift\Shift;
use Domain\Staff\Staff;

/**
 * 出勤確認通知2回目イベント.
 */
class SecondCallingEvent extends Event
{
    private Calling $calling;
    private Shift $shift;
    private Staff $staff;
    private string $url;
    private int $minutes;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param int $minutes
     * @param \Domain\Calling\Calling $calling
     * @param \Domain\Shift\Shift $shift
     * @param \Domain\Staff\Staff $staff
     * @param string $url
     */
    public function __construct(Context $context, int $minutes, Calling $calling, Shift $shift, Staff $staff, string $url)
    {
        parent::__construct($context);
        $this->minutes = $minutes;
        $this->calling = $calling;
        $this->shift = $shift;
        $this->staff = $staff;
        $this->url = $url;
    }

    /**
     * 通知時刻（何分前）の取得.
     *
     * @return int
     */
    public function minutes(): int
    {
        return $this->minutes;
    }

    /**
     * 通知の取得.
     *
     * @return \Domain\Calling\Calling
     */
    public function calling(): Calling
    {
        return $this->calling;
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
     * 2回目通知で送るURLの取得.
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }
}

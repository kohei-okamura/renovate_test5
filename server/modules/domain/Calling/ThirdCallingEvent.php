<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Context\Context;
use Domain\Event\Event;
use Domain\Staff\Staff;

/**
 * 出勤確認第三通知イベント.
 */
class ThirdCallingEvent extends Event
{
    private Staff $staff;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     */
    public function __construct(Context $context, Staff $staff)
    {
        parent::__construct($context);

        $this->staff = $staff;
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

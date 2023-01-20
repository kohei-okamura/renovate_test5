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
 * 出勤確認第四通知イベント.
 */
class FourthCallingEvent extends Event
{
    private Staff $assignerStaff;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $assignerStaff
     */
    public function __construct(Context $context, Staff $assignerStaff)
    {
        parent::__construct($context);

        $this->assignerStaff = $assignerStaff;
    }

    /**
     * 管理スタッフの取得.
     *
     * @return \Domain\Staff\Staff
     */
    public function assignerStaff(): Staff
    {
        return $this->assignerStaff;
    }
}

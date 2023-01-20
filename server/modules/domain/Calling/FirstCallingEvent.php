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
 * 通知1回目イベント.
 */
class FirstCallingEvent extends Event
{
    private Calling $calling;
    private Staff $staff;
    private string $url;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Calling\Calling $calling
     * @param \Domain\Staff\Staff $staff
     * @param string $url
     */
    public function __construct(Context $context, Calling $calling, Staff $staff, string $url)
    {
        parent::__construct($context);
        $this->calling = $calling;
        $this->staff = $staff;
        $this->url = $url;
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
     * スタッフの取得.
     *
     * @return \Domain\Staff\Staff
     */
    public function staff(): Staff
    {
        return $this->staff;
    }

    /**
     * URLの取得.
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }
}
